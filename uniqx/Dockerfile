FROM php:8.3-fpm

RUN apt update
RUN apt install -y nginx supervisor

COPY conf/nginx.conf /etc/nginx/sites-available/default
COPY conf/supervisord.conf /etc/supervisor/supervisord.conf

COPY src /var/www/html/
RUN chown -R www-data:www-data /var/www/html/public/tmp

WORKDIR /var/www/

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]