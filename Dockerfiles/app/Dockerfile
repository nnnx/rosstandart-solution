# ---------------------- PHP-FPM ----------------------
FROM php:7.4-fpm AS phpfpm

# Set timezone
ARG TIMEZONE=Europe/Moscow
ENV TZ ${TIMEZONE}
RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime && echo ${TZ} > /etc/timezone

# Install packages
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        cron \
        curl \
        git \
        locales \
        procps \
        sudo \
        unzip \
        pdftohtml \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Set locale
RUN sed -i '/en_US.UTF-8/s/^# //g' /etc/locale.gen && locale-gen
ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:en
ENV LC_ALL en_US.UTF-8

# Install PHP extensions
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    gd \
    intl \
    pdo_pgsql \
    zip

# Set php configs
COPY Dockerfiles/app/php/php-prod.ini /usr/local/etc/php/php.ini
COPY Dockerfiles/app/php/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY Dockerfiles/app/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

# Set working directory
WORKDIR /app

# Install Composer dependencies
COPY composer.* /app
RUN composer install
#--optimize-autoloader --no-interaction --no-progress

# Copy application code
COPY . /app