FROM php:8.3-apache

WORKDIR /app
COPY src /var/www

EXPOSE 80

CMD ["bash", "-c", "php /var/www/init_db.php && apache2-foreground"]
