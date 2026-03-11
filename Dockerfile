FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

COPY Liga/ /var/www/html/

RUN chown -R www-data:www-data /var/www/html

CMD ["sh", "-c", "sed -i \"s/Listen 80/Listen ${PORT:-80}/g\" /etc/apache2/ports.conf && sed -i \"s/:80/:${PORT:-80}/g\" /etc/apache2/sites-enabled/000-default.conf && apache2-foreground"]
