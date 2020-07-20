FROM ubuntu:18.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    php7.2 php7.2-common php7.2-mbstring php-bcmath php7.2-zip php7.2-curl php7.2-xml php7.2-gd \
    composer git ffmpeg

RUN mkdir -p /opt/graphjs-server
WORKDIR /opt/graphjs-server

COPY . /opt/graphjs-server

RUN composer install

EXPOSE 1338

CMD [ "php7.2", "run.php" ]
