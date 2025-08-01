FROM php:8.2-apache

# Installe extensions PHP nécessaires + GIT + Unzip
RUN apt-get update && apt-get install -y git unzip \
    && docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite

# Copie le projet
COPY . /var/www/html/

# Redirige Apache vers /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Définir le bon dossier de travail
WORKDIR /var/www/html

# Changer les permissions des dossiers Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copie composer depuis l’image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Installer les dépendances Laravel
RUN composer install --no-dev --optimize-autoloader \
    && php artisan config:cache \
    && php artisan route:cache

EXPOSE 80

RUN php artisan --version \
    && php artisan config:clear \
    && php artisan route:clear \
    && ls -la \
    && ls -la vendor
