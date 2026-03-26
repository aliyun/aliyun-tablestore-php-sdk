#!/usr/bin/env bash
# =============================================================================
# run-tests.sh — Executed inside the Docker container
#
# Responsibilities:
#   1. Copy project source into a writable workspace
#   2. Run composer install
#   3. Optionally upgrade PHPUnit for specific PHP versions
#   4. Run PHPUnit tests with coverage collection
#   5. Separate PHP errors/warnings/deprecations into dedicated files
#   6. Write structured results to the reports directory
# =============================================================================

set -o pipefail

PHP_VERSION="${PHP_VERSION:-unknown}"
TEST_MODE="${TEST_MODE:-basic}"
RESULT_DIR="/app/reports/php-${PHP_VERSION}"
WORK_DIR="/app/workspace"

echo "============================================"
echo " PHP ${PHP_VERSION} — Regression Test Runner"
echo "============================================"
echo "Test mode: ${TEST_MODE}"
echo "PHP binary: $(php -v | head -1)"
echo ""

# -----------------------------------------------------------------------------
# 1. Prepare workspace
# -----------------------------------------------------------------------------
rm -rf "${WORK_DIR}"
mkdir -p "${WORK_DIR}" "${RESULT_DIR}"

# Copy source code to writable workspace (source is mounted read-only)
cp -a /app/source/. "${WORK_DIR}/"
cd "${WORK_DIR}" || exit 1

# Remove existing vendor to ensure clean install
rm -rf vendor composer.lock

# -----------------------------------------------------------------------------
# 2. Composer install
# -----------------------------------------------------------------------------
echo ">>> Running composer install ..."
COMPOSER_INSTALL_LOG="${RESULT_DIR}/composer-install.log"

composer install --no-interaction --no-progress --prefer-dist 2>&1 | tee "${COMPOSER_INSTALL_LOG}"
COMPOSER_EXIT_CODE=${PIPESTATUS[0]}

echo "${COMPOSER_EXIT_CODE}" > "${RESULT_DIR}/composer-exit-code"

if [ "${COMPOSER_EXIT_CODE}" -ne 0 ]; then
    echo "❌ composer install failed with exit code ${COMPOSER_EXIT_CODE}"
    echo "COMPOSER_FAIL" > "${RESULT_DIR}/status"

    # Still extract errors from the composer log
    grep -iE "(error|warning|fatal|exception)" "${COMPOSER_INSTALL_LOG}" \
        > "${RESULT_DIR}/composer-errors.log" 2>/dev/null || true

    # Write summary
    cat > "${RESULT_DIR}/summary.json" <<EOF
{
    "php_version": "${PHP_VERSION}",
    "composer_install": "fail",
    "composer_exit_code": ${COMPOSER_EXIT_CODE},
    "tests_run": 0,
    "tests_passed": 0,
    "tests_failed": 0,
    "tests_errors": 0,
    "tests_skipped": 0,
    "test_exit_code": -1,
    "status": "incompatible"
}
EOF
    exit 0
fi

echo "✅ composer install succeeded"

# -----------------------------------------------------------------------------
# 3. Run PHPUnit tests
# -----------------------------------------------------------------------------
echo ""
echo ">>> Running PHPUnit tests ..."

PHPUNIT_BIN="vendor/bin/phpunit"
if [ ! -f "${PHPUNIT_BIN}" ]; then
    echo "❌ PHPUnit binary not found at ${PHPUNIT_BIN}"
    echo "PHPUNIT_NOT_FOUND" > "${RESULT_DIR}/status"
    cat > "${RESULT_DIR}/summary.json" <<EOF
{
    "php_version": "${PHP_VERSION}",
    "composer_install": "pass",
    "composer_exit_code": 0,
    "tests_run": 0,
    "tests_passed": 0,
    "tests_failed": 0,
    "tests_errors": 0,
    "tests_skipped": 0,
    "test_exit_code": -1,
    "status": "error"
}
EOF
    exit 0
fi

# Build PHPUnit arguments
PHPUNIT_ARGS=""

# Coverage arguments — use PCOV for code coverage collection
PHPUNIT_ARGS="${PHPUNIT_ARGS} --coverage-clover ${RESULT_DIR}/coverage-clover.xml"
PHPUNIT_ARGS="${PHPUNIT_ARGS} --coverage-html ${RESULT_DIR}/coverage-html"

# In basic mode, we still run the tests but they may fail due to missing
# service connections. We capture everything and let the report generator
# classify the results.
if [ "${TEST_MODE}" = "full" ]; then
    echo "Running in FULL mode (integration tests with real service)"
else
    echo "Running in BASIC mode (tests may fail due to missing service config)"
fi

# Run PHPUnit, capturing stdout and stderr separately
php -d pcov.enabled=1 \
    -d pcov.directory="${WORK_DIR}/src" \
    -d error_reporting=E_ALL \
    -d display_errors=1 \
    "${PHPUNIT_BIN}" ${PHPUNIT_ARGS} \
    > "${RESULT_DIR}/phpunit-stdout.log" 2> "${RESULT_DIR}/phpunit-stderr.log"

TEST_EXIT_CODE=$?
echo "${TEST_EXIT_CODE}" > "${RESULT_DIR}/test-exit-code"

echo ""
echo "PHPUnit exit code: ${TEST_EXIT_CODE}"

# Display test output
cat "${RESULT_DIR}/phpunit-stdout.log"

if [ -s "${RESULT_DIR}/phpunit-stderr.log" ]; then
    echo ""
    echo "--- STDERR output ---"
    cat "${RESULT_DIR}/phpunit-stderr.log"
fi

# -----------------------------------------------------------------------------
# 5. Extract and classify PHP errors/warnings/deprecations
# -----------------------------------------------------------------------------
echo ""
echo ">>> Extracting errors and warnings ..."

# Combine stdout and stderr for analysis
cat "${RESULT_DIR}/phpunit-stdout.log" "${RESULT_DIR}/phpunit-stderr.log" \
    > "${RESULT_DIR}/phpunit-combined.log"

# Extract PHP errors (Fatal error, Parse error, etc.)
grep -iE "^(PHP )?(Fatal error|Parse error|Compile error)" \
    "${RESULT_DIR}/phpunit-combined.log" \
    > "${RESULT_DIR}/php-errors.log" 2>/dev/null || true

# Extract PHP warnings
grep -iE "^(PHP )?Warning:" \
    "${RESULT_DIR}/phpunit-combined.log" \
    > "${RESULT_DIR}/php-warnings.log" 2>/dev/null || true

# Extract PHP deprecations
grep -iE "^(PHP )?Deprecated:|Deprecation:" \
    "${RESULT_DIR}/phpunit-combined.log" \
    > "${RESULT_DIR}/php-deprecations.log" 2>/dev/null || true

# Extract PHP notices
grep -iE "^(PHP )?Notice:" \
    "${RESULT_DIR}/phpunit-combined.log" \
    > "${RESULT_DIR}/php-notices.log" 2>/dev/null || true

# Count issues
ERROR_COUNT=$(wc -l < "${RESULT_DIR}/php-errors.log" 2>/dev/null | tr -d ' ')
WARNING_COUNT=$(wc -l < "${RESULT_DIR}/php-warnings.log" 2>/dev/null | tr -d ' ')
DEPRECATION_COUNT=$(wc -l < "${RESULT_DIR}/php-deprecations.log" 2>/dev/null | tr -d ' ')
NOTICE_COUNT=$(wc -l < "${RESULT_DIR}/php-notices.log" 2>/dev/null | tr -d ' ')

echo "  Errors:       ${ERROR_COUNT}"
echo "  Warnings:     ${WARNING_COUNT}"
echo "  Deprecations: ${DEPRECATION_COUNT}"
echo "  Notices:      ${NOTICE_COUNT}"

# -----------------------------------------------------------------------------
# 6. Parse PHPUnit output for test counts
# -----------------------------------------------------------------------------
# PHPUnit output formats:
#   OK (X tests, Y assertions)
#   FAILURES! Tests: X, Assertions: Y, Failures: Z.
#   ERRORS! Tests: X, Assertions: Y, Errors: Z.
#   Tests: X, Assertions: Y, Errors: Z, Failures: W, Skipped: S.

TESTS_RUN=0
TESTS_PASSED=0
TESTS_FAILED=0
TESTS_ERRORS=0
TESTS_SKIPPED=0

PHPUNIT_STDOUT=$(cat "${RESULT_DIR}/phpunit-stdout.log")

# Try to parse "OK (X tests, Y assertions)"
if echo "${PHPUNIT_STDOUT}" | grep -qE "OK \([0-9]+ tests?"; then
    TESTS_RUN=$(echo "${PHPUNIT_STDOUT}" | grep -oE "OK \([0-9]+ tests?" | grep -oE "[0-9]+")
    TESTS_PASSED=${TESTS_RUN}
fi

# Try to parse "Tests: X" line
if echo "${PHPUNIT_STDOUT}" | grep -qE "Tests: [0-9]+"; then
    RESULT_LINE=$(echo "${PHPUNIT_STDOUT}" | grep -E "Tests: [0-9]+" | tail -1)
    TESTS_RUN=$(echo "${RESULT_LINE}" | grep -oE "Tests: [0-9]+" | grep -oE "[0-9]+")

    if echo "${RESULT_LINE}" | grep -qE "Failures: [0-9]+"; then
        TESTS_FAILED=$(echo "${RESULT_LINE}" | grep -oE "Failures: [0-9]+" | grep -oE "[0-9]+")
    fi
    if echo "${RESULT_LINE}" | grep -qE "Errors: [0-9]+"; then
        TESTS_ERRORS=$(echo "${RESULT_LINE}" | grep -oE "Errors: [0-9]+" | grep -oE "[0-9]+")
    fi
    if echo "${RESULT_LINE}" | grep -qE "Skipped: [0-9]+"; then
        TESTS_SKIPPED=$(echo "${RESULT_LINE}" | grep -oE "Skipped: [0-9]+" | grep -oE "[0-9]+")
    fi

    TESTS_PASSED=$((TESTS_RUN - TESTS_FAILED - TESTS_ERRORS - TESTS_SKIPPED))
    if [ "${TESTS_PASSED}" -lt 0 ]; then
        TESTS_PASSED=0
    fi
fi

# Determine overall status
STATUS="compatible"
if [ "${COMPOSER_EXIT_CODE}" -ne 0 ]; then
    STATUS="incompatible"
elif [ "${TEST_EXIT_CODE}" -ne 0 ]; then
    if [ "${TESTS_FAILED}" -gt 0 ] || [ "${TESTS_ERRORS}" -gt 0 ]; then
        STATUS="partial"
    else
        STATUS="error"
    fi
fi

# Write summary JSON
cat > "${RESULT_DIR}/summary.json" <<EOF
{
    "php_version": "${PHP_VERSION}",
    "composer_install": "pass",
    "composer_exit_code": 0,
    "tests_run": ${TESTS_RUN},
    "tests_passed": ${TESTS_PASSED},
    "tests_failed": ${TESTS_FAILED},
    "tests_errors": ${TESTS_ERRORS},
    "tests_skipped": ${TESTS_SKIPPED},
    "test_exit_code": ${TEST_EXIT_CODE},
    "php_errors": ${ERROR_COUNT},
    "php_warnings": ${WARNING_COUNT},
    "php_deprecations": ${DEPRECATION_COUNT},
    "php_notices": ${NOTICE_COUNT},
    "status": "${STATUS}"
}
EOF

echo ""
echo "============================================"
echo " PHP ${PHP_VERSION} — Test Summary"
echo "============================================"
echo "  Status:       ${STATUS}"
echo "  Tests run:    ${TESTS_RUN}"
echo "  Passed:       ${TESTS_PASSED}"
echo "  Failed:       ${TESTS_FAILED}"
echo "  Errors:       ${TESTS_ERRORS}"
echo "  Skipped:      ${TESTS_SKIPPED}"
echo "  Exit code:    ${TEST_EXIT_CODE}"
echo "============================================"
echo ""
echo "Results written to: ${RESULT_DIR}/"
