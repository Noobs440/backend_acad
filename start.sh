#!/bin/bash
# Installer les dépendances
composer install --no-dev --optimize-autoloader

# Générer la clé Laravel (si jamais perdue)
php artisan key:generate --force

# Migrations et seed (optionnel)
php artisan migrate:fresh --seed --force

# Optimiser Laravel
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Lancer le serveur PHP
php artisan serve --host=0.0.0.0 --port=8000