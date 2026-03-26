# PHP Multi-Version Regression Test Suite

A Docker-based regression testing tool for validating the Aliyun TableStore PHP SDK across multiple PHP versions.

## Features

- **Multi-version testing**: Automatically tests against PHP 8.2, 8.3, and 8.4
- **Isolated environments**: Each PHP version runs in its own Docker container
- **Compatibility report**: Generates a version compatibility matrix (open-source standard format)
- **Developer fix report**: Collects errors/warnings, deduplicates them, counts occurrences, and provides fix suggestions
- **Coverage report**: Per-file code coverage analysis with low-coverage highlighting (< 60% threshold)
- **Dual test modes**: Basic compatibility checks or full integration tests with real services

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/) (20.10+)
- [Docker Compose](https://docs.docker.com/compose/install/) (v2 plugin or standalone)

## Quick Start

```bash
# Run from the project root
cd regression

# Run all PHP versions (basic mode)
bash run.sh

# Run specific versions only
bash run.sh --versions "8.2 8.3"

# Run with full integration tests (requires credentials)
bash run.sh --full

# Clean up Docker images and reports
bash run.sh --clean
```

## Command Options

| Option | Description | Default |
|--------|-------------|---------|
| `--versions "X.Y ..."` | PHP versions to test | `"8.2 8.3 8.4"` |
| `--full` | Enable full integration test mode | Disabled (basic mode) |
| `--no-coverage` | Disable code coverage collection | Coverage enabled |
| `--clean` | Remove reports and Docker images | - |
| `--help` | Show help message | - |

## Test Modes

### Basic Mode (default)

Runs `composer install` and `php vendor/bin/phpunit` without service credentials. Tests that require a real TableStore connection will fail, but this mode is useful for:

- Verifying PHP version compatibility
- Detecting syntax errors and deprecations
- Collecting PHP warnings and notices
- Measuring code coverage of unit tests

### Full Mode (`--full`)

Requires a `.env` file in the project root with TableStore credentials:

```env
SDK_TEST_ACCESS_KEY_ID=your_access_key_id
SDK_TEST_ACCESS_KEY_SECRET=your_access_key_secret
SDK_TEST_END_POINT=https://your-instance.cn-hangzhou.ots.aliyuncs.com
SDK_TEST_INSTANCE_NAME=your_instance_name
```

Then run:

```bash
bash run.sh --full
```

## Generated Reports

Reports are saved to `regression/reports/`:

### 1. Compatibility Report (`compatibility-report.md`)

A version compatibility matrix showing:

| PHP Version | Composer Install | Tests Run | Tests Passed | Status |
|:-----------:|:----------------:|:---------:|:------------:|:------:|
| 8.2 | ✅ Pass | 36 | 36 | ⚠️ Partial |
| 8.5 | ✅ Pass | 36 | 35 | ✅ Compatible |

Includes a recommendation for the minimum compatible PHP version to set in `composer.json`.

### 2. Developer Fix Report (`developer-fix-report.md`)

Aggregates all PHP errors, warnings, deprecations, and notices across all tested versions:

- **Deduplication**: Groups similar issues (ignoring file paths and line numbers)
- **Occurrence counting**: Shows how many times each issue appears per PHP version
- **Fix suggestions**: Provides concrete code examples for fixing each issue type
- **Severity classification**: Organized by Error > Warning > Deprecation > Notice

### 3. Coverage Report (`coverage-report.md`)

Per-file code coverage analysis:

- Files sorted by coverage percentage (high to low)
- Files below 60% coverage highlighted with ⚠️ **LOW**
- Overall project coverage summary
- Cross-version coverage comparison (when multiple versions have data)

## Directory Structure

```
regression/
├── Dockerfile                    # Parameterized PHP test image
├── docker-compose.yml            # Multi-version service definitions
├── run.sh                        # Main entry point script
├── scripts/
│   ├── run-tests.sh              # Container-internal test runner
│   └── generate-reports.sh       # Report generation logic
├── reports/                      # Generated reports (gitignored)
│   ├── compatibility-report.md
│   ├── developer-fix-report.md
│   ├── coverage-report.md
│   └── php-X.Y/                  # Per-version raw results
│       ├── summary.json
│       ├── phpunit-stdout.log
│       ├── phpunit-stderr.log
│       ├── php-errors.log
│       ├── php-warnings.log
│       ├── php-deprecations.log
│       ├── php-notices.log
│       └── coverage-clover.xml
└── README.md                     # This file
```

## Extending

### Adding a new PHP version

1. Add a new service in `docker-compose.yml`:

```yaml
  php85:
    build:
      context: .
      dockerfile: Dockerfile
      args:
        PHP_VERSION: "8.5"
    <<: *common-config
    environment:
      - PHP_VERSION=8.5
      - TEST_MODE=${TEST_MODE:-basic}
```

2. Update the default versions in `run.sh`:

```bash
DEFAULT_VERSIONS="8.2 8.3 8.4 8.5"
```

### Customizing fix suggestions

Edit the `FIX_RULES` array in `scripts/generate-reports.sh`. Each rule follows the format:

```
PATTERN|SEVERITY|SUGGESTION
```

- `PATTERN`: grep-compatible regex to match error messages
- `SEVERITY`: `error`, `warning`, `deprecation`, or `notice`
- `SUGGESTION`: Markdown-formatted fix recommendation

## Troubleshooting

### Docker build fails

Ensure Docker is running and you have internet access for downloading PHP images and Composer packages.

### Tests fail in basic mode

This is expected for integration tests that require a real TableStore connection. Check the compatibility report — if `composer install` passes, the SDK is compatible with that PHP version.

### No coverage data

Ensure the PCOV extension is successfully installed in the Docker image. Check the Docker build logs for any PCOV installation errors.

---

*Part of the [Aliyun TableStore PHP SDK](https://github.com/aliyun/aliyun-tablestore-php-sdk)*
