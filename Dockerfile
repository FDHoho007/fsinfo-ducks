FROM php:8.4-apache
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite
RUN mkdir /var/www/html/uploads
COPY assets/ /var/www/html
COPY includes/ /var/www/html
COPY .htaccess /var/www/html
COPY api.php /var/www/html
COPY atom.php /var/www/html
COPY duck.php /var/www/html
COPY index.php /var/www/html
VOLUME /var/www/html/uploads