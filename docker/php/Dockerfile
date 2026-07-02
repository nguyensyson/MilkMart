FROM php:8.2-fpm

ARG ORACLE_INSTANTCLIENT_VERSION=23.4.0.24.05
ARG ORACLE_INSTANTCLIENT_MAJOR=2340000

# System dependencies + PHP extensions required by Laravel/Inertia/Oracle
RUN apt-get update && apt-get install -y --no-install-recommends \
        unzip \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libicu-dev \
        git \
        curl \
    && (apt-get install -y --no-install-recommends libaio1t64 || apt-get install -y --no-install-recommends libaio1) \
    # Oracle Instant Client links against the old SONAME "libaio.so.1"; Debian's
    # 64-bit time_t transition renamed the shipped library to "libaio.so.1t64".
    && (ln -sf /usr/lib/x86_64-linux-gnu/libaio.so.1t64 /usr/lib/x86_64-linux-gnu/libaio.so.1 2>/dev/null || true) \
    && ldconfig \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
    && rm -rf /var/lib/apt/lists/*

# Oracle Instant Client (Basic + SDK) — required for the oci8 PHP extension.
# Downloaded directly from Oracle's public CDN (no login required).
RUN mkdir -p /opt/oracle \
    && curl -sSL -o /tmp/instantclient-basic.zip \
        "https://download.oracle.com/otn_software/linux/instantclient/${ORACLE_INSTANTCLIENT_MAJOR}/instantclient-basic-linux.x64-${ORACLE_INSTANTCLIENT_VERSION}.zip" \
    && curl -sSL -o /tmp/instantclient-sdk.zip \
        "https://download.oracle.com/otn_software/linux/instantclient/${ORACLE_INSTANTCLIENT_MAJOR}/instantclient-sdk-linux.x64-${ORACLE_INSTANTCLIENT_VERSION}.zip" \
    && unzip -q -o /tmp/instantclient-basic.zip -d /opt/oracle \
    && unzip -q -o /tmp/instantclient-sdk.zip -d /opt/oracle \
    && rm -f /tmp/instantclient-basic.zip /tmp/instantclient-sdk.zip \
    && INSTANTCLIENT_DIR=$(find /opt/oracle -maxdepth 1 -type d -name 'instantclient_*') \
    && ln -s "${INSTANTCLIENT_DIR}" /opt/oracle/instantclient \
    && ln -s /opt/oracle/instantclient/libclntsh.so.* /opt/oracle/instantclient/libclntsh.so 2>/dev/null || true \
    && ln -s /opt/oracle/instantclient/libocci.so.* /opt/oracle/instantclient/libocci.so 2>/dev/null || true \
    && echo "/opt/oracle/instantclient" > /etc/ld.so.conf.d/oracle-instantclient.conf \
    && ldconfig

ENV ORACLE_HOME=/opt/oracle/instantclient
ENV LD_LIBRARY_PATH=/opt/oracle/instantclient:$LD_LIBRARY_PATH

# oci8 PHP extension (yajra/laravel-oci8 uses ext-oci8 directly, no pdo_oci needed)
RUN echo 'instantclient,/opt/oracle/instantclient' | pecl install oci8 \
    && docker-php-ext-enable oci8

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY php.ini /usr/local/etc/php/conf.d/99-milkmart.ini

WORKDIR /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
