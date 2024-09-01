FROM php:8.3-apache

RUN apt update
RUN apt install -y supervisor
RUN apt install -y python3-flask
RUN apt install -y golang-go

COPY config/supervisor/flask.conf /etc/supervisor/conf.d/
COPY config/supervisor/goapi.conf /etc/supervisor/conf.d/

COPY src /var/www/

COPY config/supervisor/flask.conf /etc/supervisor/conf.d/
COPY config/apache/000-default.conf /etc/apache2/sites-available/

WORKDIR /var/www/front
RUN ln -s /var/www/front /app
RUN go build -o goapi main.go

RUN a2enmod headers proxy proxy_http

EXPOSE 80

CMD echo '127.0.0.1 proxy.internal fileserver.internal' >> /etc/hosts; service supervisor start; apache2-foreground
