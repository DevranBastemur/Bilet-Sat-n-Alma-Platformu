FROM php:8.1-apache

RUN apt-get update && apt-get install -y sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite iconv

RUN a2enmod rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/config \
    && chmod -R 775 /var/www/html/config

RUN chmod +x /var/www/html/docker-entrypoint.sh

CMD ["/var/www/html/docker-entrypoint.sh"]
