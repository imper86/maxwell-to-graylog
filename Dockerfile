FROM php:8.2-cli

ENV APP_ENV=prod

RUN apt-get update && \
    apt-get install -y --no-install-recommends supervisor tzdata git libzip-dev zip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions amqp curl intl json redis yaml zip sockets

WORKDIR /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /app
COPY ./etc/supervisor /etc/supervisor

RUN /usr/local/bin/composer install --no-dev

RUN touch /var/log/soutput.log

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf", "-i", "app", "-n"]
