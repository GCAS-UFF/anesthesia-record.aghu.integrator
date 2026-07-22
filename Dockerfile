FROM php:8.3-apache

# Instala dependências do PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Instala as extensões PHP
RUN docker-php-ext-install pdo pdo_pgsql

# Habilita mod_rewrite
RUN a2enmod rewrite

# Copia a aplicação
COPY . /var/www/html

# Define a pasta public como DocumentRoot
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf

# Permite o uso do .htaccess
RUN echo '<Directory /var/www/html/public>\n\
AllowOverride All\n\
Require all granted\n\
</Directory>' > /etc/apache2/conf-available/app.conf \
    && a2enconf app

EXPOSE 80