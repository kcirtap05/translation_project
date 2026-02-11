#!/bin/sh
php /app/artisan cache:clear
php /app/artisan config:clear
php /app/artisan optimize:clear

php /app/artisan migrate --force --path=database/migrations/dev

sed -i "s,LISTEN_PORT,9015,g" /etc/nginx/nginx.conf

php-fpm -D

while ! nc -w 1 -z 0.0.0.0 9000; do sleep 0.1; done;

nginx