FROM php:8.3-apache
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite
COPY index.php /var/www/html
COPY duck.php /var/www/html
COPY lib.php /var/www/html
COPY style.css /var/www/html
COPY img /var/www/html
VOLUME /var/www/html/ducks