FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

COPY Liga/ /var/www/html/

RUN chown -R www-data:www-data /var/www/html

RUN echo '\n\
ServerName localhost\n\
<VirtualHost *:${PORT}>\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' >> /etc/apache2/sites-available/000-default.conf

RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf

CMD ["sh", "-c", "PORT=${PORT:-80} apache2-foreground"]
