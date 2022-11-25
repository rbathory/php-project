FROM php:7.4-apache

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install mysqli \
    && apt update && apt -y install net-tools telnet
