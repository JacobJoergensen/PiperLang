FROM php:8.3-cli

RUN apt-get update && apt-get upgrade -y && apt-get install -y libicu-dev
RUN docker-php-ext-install intl
WORKDIR /PiperLang

COPY . /PiperLang

RUN curl --silent --show-error https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

RUN composer install