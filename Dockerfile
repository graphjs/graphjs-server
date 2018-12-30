FROM ubuntu:16.04

RUN     apt-get update && \
        apt-get upgrade -y && \
        apt-get install -y php7.1 php7.1-bcmath php7.1-cli php7.1-common \
        php7.1-devphp7.1-fpm php7.1-gd php7.1-intl \
        php7.1-json  php7.1-mbstring php7.1-mcrypt php7.1-mysqlphp7.1-opcache \
        php7.1-readline  php7.1-xml php7.1-zip \
        redis-server php-redis neo4j composer

RUN mkdir -p /opt/graphjs-server
WORKDIR /opt/graphjs-server

COPY . /opt/graphjs-server
 
CMD [ "composer", "install" ]

EXPOSE 8080

CMD [ "php7.1", "run.php" ]
