FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_pgsql

RUN a2enmod rewrite

COPY . /var/www/html

RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

RUN printf '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' > /etc/apache2/conf-available/app.conf \
    && a2enconf app

EXPOSE 80