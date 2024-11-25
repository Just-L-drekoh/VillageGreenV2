FROM php:8.3-apache

RUN a2enmod rewrite

RUN apt-get update
RUN apt install -y libzip-dev git wget --no-install-recommends
 
RUN docker-php-ext-install pdo mysqli pdo_mysql zip;


# Install OPCache
RUN docker-php-ext-install opcache

# Add an OPCache configuration file
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Install nvm
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.0/install.sh | bash \
    && export NVM_DIR="/root/.nvm" \
    && [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh" \
    && nvm install 22
    
RUN wget https://getcomposer.org/installer -O /var/www/composer-setup.php
RUN php /var/www/composer-setup.php 
RUN mv composer.phar /usr/bin/composer 
RUN chmod +x /usr/bin/composer
 
COPY apache.conf /etc/apache2/sites-enabled/000-default.conf
 
WORKDIR /var/www

# Attribuer tous les droits à /var/www
RUN chmod -R 777 /var/www

# Assurer que l'utilisateur www-data possède tous les fichiers
RUN chown -R www-data:www-data /var/www