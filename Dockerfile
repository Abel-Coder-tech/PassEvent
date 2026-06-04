FROM richarvey/nginx-php-fpm:latest

USER root

# Copier tous les fichiers du projet
COPY . .

# Créer les dossiers de cache Laravel
RUN mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/framework/testing

# Droits d'écriture sur storage
RUN chown -R www-data:www-data storage \
    && chmod -R 775 storage

# Variables d'environnement
ENV SKIP_COMPOSER 0
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV APP_ENV production
ENV APP_DEBUG false

# Autoriser Composer en root
ENV COMPOSER_ALLOW_SUPERUSER 1

# Installer les dépendances PHP
RUN composer install --optimize-autoloader --no-dev

# Optimisations Laravel (cache config, routes, vues)
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Commande de démarrage
CMD ["/start.sh"]