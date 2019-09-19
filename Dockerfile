FROM ubuntu:18.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt install software-properties-common && \
    add-apt-repository ppa:ondrej/php && \
    apt-get update && \
    apt-get install -y --no-install-recommends \
    php7.3 php7.3-common php7.3-mbstring php-bcmath \
    php7.3-zip php7.3-curl php7.3-xml php7.3-gd \
    php7.3-dev composer git ffmpeg

RUN mkdir -p /opt/graphjs-server
WORKDIR /opt/graphjs-server

COPY . /opt/graphjs-server

RUN composer install

EXPOSE 1338

CMD [ "php7.3", "run.php" ]
