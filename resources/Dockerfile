FROM php:8.1-fpm-alpine

RUN apk add --no-cache bash nginx wget oniguruma-dev

RUN apk update
RUN apk add nano
# Clear cache
RUN rm -rf /var/cache/apk/*

ENV ACCEPT_EULA=Y

# Install prerequisites required for tools and extensions installed later on.
RUN apk add --update bash gnupg less libpng-dev libzip-dev su-exec unzip

# Install extensions
# Make it readable and arrange from A-Z
RUN docker-php-ext-install \
    bcmath \
    exif \
    mbstring \
    opcache \
    pdo \
    pdo_mysql \
    pcntl \
    gd \
    zip 

RUN mkdir -p /run/nginx

# COPY php usr/local/lib/php/extensions/no-debug-non-zts-20170718
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/www.conf /usr/local/etc/php-fpm.d/www.conf

RUN mkdir -p /app
COPY . /app

RUN sh -c "wget http://getcomposer.org/composer.phar && chmod a+x composer.phar && mv composer.phar /usr/local/bin/composer"
RUN cd /app && \
    /usr/local/bin/composer install --no-dev

COPY .env.dev /app/.env

RUN chown -R www-data: /app
RUN chown -R www-data: /app/storage
RUN chmod 775 -R /app/storage
ADD ./docker/custom-php.ini /usr/local/etc/php/conf.d/custom-php.ini

EXPOSE 9015

ENTRYPOINT [ "sh",  "/app/docker/startup.dev.sh"]