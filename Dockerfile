# Utiliser l'image PHP officielle avec Apache
FROM php:8.2-apache

# Installer les extensions nécessaires pour Laravel
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install \
    pdo_mysql \
    zip \
    intl

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier les fichiers du projet
COPY . /var/www/html

# Configurer le répertoire de travail
WORKDIR /var/www/html

# Installer les dépendances PHP
RUN composer install --optimize-autoloader --no-dev

# Définir les permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# Configurer Apache pour pointer vers le dossier public
RUN a2enmod rewrite
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Exposer le port 80
EXPOSE 80