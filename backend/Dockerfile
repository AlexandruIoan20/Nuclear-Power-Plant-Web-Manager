FROM php:8.4-apache

RUN apt-get update && apt-get install -y libpq-dev libzip-dev supervisor \
    && docker-php-ext-install pdo pdo_pgsql pgsql pcntl zip

RUN a2enmod rewrite

RUN sed -i 's|/var/www/html|/var/www/html/public|g' \
      /etc/apache2/sites-available/000-default.conf

RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' \
      /etc/apache2/apache2.conf

RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY . /var/www/html

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN chown -R www-data:www-data /var/www/html

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]