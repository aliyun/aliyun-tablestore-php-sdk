#!/usr/bin/env bash
# =============================================================================
# run.sh — PHP Multi-Version Regression Test Runner
#
# Usage:
#   bash run.sh                          # Run all versions (8.2, 8.3, 8.4, 8.5)
#   bash run.sh --versions "8.2 8.3"     # Run specific versions
#   bash run.sh --full                   # Run with full integration tests
#   bash run.sh --no-coverage            # Disable coverage collection
#   bash run.sh --help                   # Show help
#
# Prerequisites:
#   - Docker and Docker Compose installed and running
# =============================================================================

set -uo pipefail
# Note: We intentionally do NOT use 'set -e' because Docker commands may
# return non-zero exit codes that we handle explicitly (e.g., test failures,
# build failures). Using 'set -e' causes silent exits in subshells.

# Script directory (regression/)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
REPORTS_DIR="${SCRIPT_DIR}/reports"
GENERATE_SCRIPT="${SCRIPT_DIR}/scripts/generate-reports.sh"

# =============================================================================
# Auto-detect Docker path (macOS Docker Desktop may not be in PATH)
# =============================================================================
if ! command -v docker &> /dev/null; then
    DOCKER_DESKTOP_BIN="/Applications/Docker.app/Contents/Resources/bin"
    if [ -x "${DOCKER_DESKTOP_BIN}/docker" ]; then
        export PATH="${DOCKER_DESKTOP_BIN}:${PATH}"
        # Also add cli-plugins directory for docker compose
        DOCKER_CLI_PLUGINS="/Applications/Docker.app/Contents/Resources/cli-plugins"
        if [ -d "${DOCKER_CLI_PLUGINS}" ]; then
            mkdir -p "${HOME}/.docker/cli-plugins" 2>/dev/null || true
            # Symlink compose plugin if not already present
            if [ ! -e "${HOME}/.docker/cli-plugins/docker-compose" ] && [ -f "${DOCKER_CLI_PLUGINS}/docker-compose" ]; then
                ln -sf "${DOCKER_CLI_PLUGINS}/docker-compose" "${HOME}/.docker/cli-plugins/docker-compose" 2>/dev/null || true
            fi
        fi
    fi
fi

# Default configuration
DEFAULT_VERSIONS="8.2 8.3 8.4 8.5"
VERSIONS="${DEFAULT_VERSIONS}"
TEST_MODE="basic"
COVERAGE_ENABLED=true

# Color codes for terminal output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color
BOLD='\033[1m'

# =============================================================================
# Functions
# =============================================================================

usage() {
    cat <<EOF
${BOLD}PHP Multi-Version Regression Test Runner${NC}

${BOLD}USAGE:${NC}
    bash run.sh [OPTIONS]

${BOLD}OPTIONS:${NC}
    --versions "X.Y ..."   PHP versions to test (default: "${DEFAULT_VERSIONS}")
    --full                  Enable full integration test mode (requires .env with credentials)
    --no-coverage           Disable code coverage collection
    --clean                 Clean reports directory and Docker images, then exit
    --help                  Show this help message

${BOLD}EXAMPLES:${NC}
    bash run.sh                          # Test all supported PHP versions
    bash run.sh --versions "8.2 8.3"     # Test only PHP 8.2 and 8.3
    bash run.sh --full                   # Run full integration tests
    bash run.sh --versions "8.2" --full  # Full tests on PHP 8.2 only

${BOLD}REPORTS:${NC}
    Reports are generated in: regression/reports/
    - compatibility-report.md    PHP version compatibility matrix
    - developer-fix-report.md    Errors/warnings with fix suggestions
    - coverage-report.md         Code coverage by file

${BOLD}FULL MODE:${NC}
    Create a .env file in the project root with:
        SDK_TEST_ACCESS_KEY_ID=your_key_id
        SDK_TEST_ACCESS_KEY_SECRET=your_key_secret
        SDK_TEST_END_POINT=your_endpoint
        SDK_TEST_INSTANCE_NAME=your_instance
EOF
}

log_info() {
    echo -e "${BLUE}[INFO]${NC} $*"
}

log_success() {
    echo -e "${GREEN}[PASS]${NC} $*"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $*"
}

log_error() {
    echo -e "${RED}[FAIL]${NC} $*"
}

log_header() {
    echo ""
    echo -e "${CYAN}${BOLD}═══════════════════════════════════════════════════════${NC}"
    echo -e "${CYAN}${BOLD}  $*${NC}"
    echo -e "${CYAN}${BOLD}═══════════════════════════════════════════════════════${NC}"
    echo ""
}

check_prerequisites() {
    local missing=false

    # Check docker command (use || true to prevent set -e from exiting)
    local docker_found=false
    command -v docker &> /dev/null && docker_found=true
    if [ "${docker_found}" = false ]; then
        log_error "Docker is not installed or not in PATH"
        missing=true
    fi

    # Check docker compose (use subshell to isolate errors)
    if [ "${docker_found}" = true ]; then
        local compose_found=false
        (docker compose version &> /dev/null) && compose_found=true
        if [ "${compose_found}" = false ]; then
            (docker-compose version &> /dev/null) && compose_found=true
        fi
        if [ "${compose_found}" = false ]; then
            log_error "Docker Compose is not installed or not in PATH"
            missing=true
        fi

        # Check docker daemon
        local daemon_running=false
        (docker info &> /dev/null) && daemon_running=true
        if [ "${daemon_running}" = false ]; then
            log_error "Docker daemon is not running"
            missing=true
        fi
    fi

    if [ "${missing}" = true ]; then
        echo ""
        log_error "Please install missing prerequisites and try again."
        exit 1
    fi

    log_success "Prerequisites check passed"
}

# Detect docker compose command (v2 plugin vs standalone)
get_compose_cmd() {
    local v2_ok=false
    (docker compose version &> /dev/null) && v2_ok=true
    if [ "${v2_ok}" = true ]; then
        echo "docker compose"
        return
    fi

    local v1_ok=false
    (docker-compose version &> /dev/null) && v1_ok=true
    if [ "${v1_ok}" = true ]; then
        echo "docker-compose"
        return
    fi

    log_error "Docker Compose not found"
    exit 1
}

# Map version string to docker-compose service name
version_to_service() {
    local version="$1"
    echo "php${version//./}"
}

clean_all() {
    log_header "Cleaning up"

    log_info "Removing reports directory ..."
    rm -rf "${REPORTS_DIR}"
    mkdir -p "${REPORTS_DIR}"

    log_info "Removing Docker images ..."
    local compose_cmd
    compose_cmd=$(get_compose_cmd)
    cd "${SCRIPT_DIR}"
    ${compose_cmd} -f docker-compose.yml down --rmi local --volumes --remove-orphans 2>/dev/null || true

    log_success "Cleanup complete"
}

# =============================================================================
# Parse arguments
# =============================================================================

while [[ $# -gt 0 ]]; do
    case $1 in
        --versions)
            VERSIONS="$2"
            shift 2
            ;;
        --full)
            TEST_MODE="full"
            shift
            ;;
        --no-coverage)
            COVERAGE_ENABLED=false
            shift
            ;;
        --clean)
            clean_all
            exit 0
            ;;
        --help|-h)
            usage
            exit 0
            ;;
        *)
            log_error "Unknown option: $1"
            usage
            exit 1
            ;;
    esac
done

# =============================================================================
# Main execution
# =============================================================================

log_header "PHP Multi-Version Regression Test"

echo -e "  ${BOLD}Project:${NC}    $(basename "${PROJECT_ROOT}")"
echo -e "  ${BOLD}Versions:${NC}   ${VERSIONS}"
echo -e "  ${BOLD}Test mode:${NC}  ${TEST_MODE}"
echo -e "  ${BOLD}Coverage:${NC}   ${COVERAGE_ENABLED}"
echo -e "  ${BOLD}Reports:${NC}    ${REPORTS_DIR}"
echo ""

# Check prerequisites
check_prerequisites

# Clean previous reports
log_info "Cleaning previous reports ..."
rm -rf "${REPORTS_DIR}"
mkdir -p "${REPORTS_DIR}"

# Get compose command
COMPOSE_CMD=$(get_compose_cmd)
cd "${SCRIPT_DIR}"

# Track overall results
TOTAL_VERSIONS=0
PASSED_VERSIONS=0
FAILED_VERSIONS=0
declare -a VERSION_RESULTS=()

# Build and run tests for each PHP version
for VERSION in ${VERSIONS}; do
    TOTAL_VERSIONS=$((TOTAL_VERSIONS + 1))
    SERVICE=$(version_to_service "${VERSION}")

    log_header "Testing PHP ${VERSION}"

    # Build the image
    log_info "Building Docker image for PHP ${VERSION} ..."
    if ! ${COMPOSE_CMD} -f docker-compose.yml build --no-cache "${SERVICE}" 2>&1; then
        log_error "Failed to build image for PHP ${VERSION}"
        FAILED_VERSIONS=$((FAILED_VERSIONS + 1))
        VERSION_RESULTS+=("${VERSION}:build_fail")

        # Write a minimal summary for build failures
        mkdir -p "${REPORTS_DIR}/php-${VERSION}"
        cat > "${REPORTS_DIR}/php-${VERSION}/summary.json" <<EOF
{
    "php_version": "${VERSION}",
    "composer_install": "n/a",
    "composer_exit_code": -1,
    "tests_run": 0,
    "tests_passed": 0,
    "tests_failed": 0,
    "tests_errors": 0,
    "tests_skipped": 0,
    "test_exit_code": -1,
    "php_errors": 0,
    "php_warnings": 0,
    "php_deprecations": 0,
    "php_notices": 0,
    "status": "build_fail"
}
EOF
        continue
    fi

    log_success "Image built for PHP ${VERSION}"

    # Run the tests
    log_info "Running tests on PHP ${VERSION} ..."
    CONTAINER_EXIT_CODE=0
    ${COMPOSE_CMD} -f docker-compose.yml run --rm \
        -e "TEST_MODE=${TEST_MODE}" \
        -e "PHP_VERSION=${VERSION}" \
        "${SERVICE}" 2>&1 || CONTAINER_EXIT_CODE=$?

    # Check results
    RESULT_DIR="${REPORTS_DIR}/php-${VERSION}"
    if [ -f "${RESULT_DIR}/summary.json" ]; then
        STATUS=$(grep -o '"status": *"[^"]*"' "${RESULT_DIR}/summary.json" | head -1 | cut -d'"' -f4)
        case "${STATUS}" in
            compatible)
                log_success "PHP ${VERSION}: Compatible"
                PASSED_VERSIONS=$((PASSED_VERSIONS + 1))
                ;;
            partial)
                log_warn "PHP ${VERSION}: Partially compatible"
                FAILED_VERSIONS=$((FAILED_VERSIONS + 1))
                ;;
            *)
                log_error "PHP ${VERSION}: ${STATUS}"
                FAILED_VERSIONS=$((FAILED_VERSIONS + 1))
                ;;
        esac
        VERSION_RESULTS+=("${VERSION}:${STATUS}")
    else
        log_error "PHP ${VERSION}: No results found"
        FAILED_VERSIONS=$((FAILED_VERSIONS + 1))
        VERSION_RESULTS+=("${VERSION}:no_results")
    fi

    echo ""
done

# =============================================================================
# Generate reports
# =============================================================================

log_header "Generating Reports"

if [ -f "${GENERATE_SCRIPT}" ]; then
    bash "${GENERATE_SCRIPT}" "${REPORTS_DIR}"
    log_success "Reports generated successfully"
else
    log_error "Report generator script not found: ${GENERATE_SCRIPT}"
fi

# =============================================================================
# Print summary
# =============================================================================

log_header "Regression Test Summary"

echo -e "  ${BOLD}Total versions tested:${NC}  ${TOTAL_VERSIONS}"
echo -e "  ${BOLD}Passed:${NC}                ${GREEN}${PASSED_VERSIONS}${NC}"
echo -e "  ${BOLD}Failed:${NC}                ${RED}${FAILED_VERSIONS}${NC}"
echo ""

echo -e "  ${BOLD}Results per version:${NC}"
for RESULT in "${VERSION_RESULTS[@]}"; do
    VER="${RESULT%%:*}"
    STAT="${RESULT##*:}"
    case "${STAT}" in
        compatible)
            echo -e "    PHP ${VER}: ${GREEN}✅ Compatible${NC}"
            ;;
        partial)
            echo -e "    PHP ${VER}: ${YELLOW}⚠️  Partially compatible${NC}"
            ;;
        incompatible|build_fail)
            echo -e "    PHP ${VER}: ${RED}❌ Incompatible${NC}"
            ;;
        *)
            echo -e "    PHP ${VER}: ${RED}❌ ${STAT}${NC}"
            ;;
    esac
done

echo ""
echo -e "  ${BOLD}Reports:${NC}"
echo -e "    📄 ${REPORTS_DIR}/compatibility-report.md"
echo -e "    📄 ${REPORTS_DIR}/developer-fix-report.md"
echo -e "    📄 ${REPORTS_DIR}/coverage-report.md"
echo ""

# Clean up containers
log_info "Cleaning up containers ..."
cd "${SCRIPT_DIR}"
${COMPOSE_CMD} -f docker-compose.yml down --remove-orphans 2>/dev/null || true

if [ "${FAILED_VERSIONS}" -gt 0 ]; then
    exit 1
fi
exit 0
