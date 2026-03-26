ARG PHP_VERSION=8.2

FROM php:${PHP_VERSION}-cli

LABEL maintainer="Aliyun Tablestore Team"
LABEL description="PHP SDK regression test image"

# Install system dependencies
RUN sed -i 's/deb.debian.org/mirrors.aliyun.com/g' /etc/apt/sources.list.d/debian.sources 2>/dev/null || \
    sed -i 's|http://deb.debian.org|http://mirrors.aliyun.com|g' /etc/apt/sources.list && \
    apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    && docker-php-ext-install zip curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PCOV for code coverage (much faster than Xdebug)
RUN pecl install pcov && docker-php-ext-enable pcov

# Configure PCOV
RUN echo "pcov.enabled=1" >> /usr/local/etc/php/conf.d/docker-php-ext-pcov.ini \
    && echo "pcov.directory=/app/src" >> /usr/local/etc/php/conf.d/docker-php-ext-pcov.ini

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy the test runner script
COPY scripts/run-tests.sh /usr/local/bin/run-tests.sh
RUN chmod +x /usr/local/bin/run-tests.sh

ENTRYPOINT ["bash", "/usr/local/bin/run-tests.sh"]
