FROM node:10

RUN echo "deb [trusted=yes] http://archive.debian.org/debian stretch main" > /etc/apt/sources.list
RUN echo "deb [trusted=yes] http://archive.debian.org/debian stretch-backports main" >> /etc/apt/sources.list
RUN echo "deb [trusted=yes] http://archive.debian.org/debian-security stretch/updates main" >> /etc/apt/sources.list
RUN apt update && apt install -y cowsay

COPY src /app

RUN rm /usr/share/cowsay/cows/*
RUN mv /app/default.cow /usr/share/cowsay/cows/
RUN mv /usr/games/cowsay /usr/bin/barbhack

WORKDIR /app

RUN chmod 0644 flag.here webapp.js

USER node

EXPOSE 3000

CMD [ "node", "webapp.js" ]
