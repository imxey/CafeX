FROM dunglas/frankenphp:latest

RUN docker-php-ext-install pdo pdo_mysql
