# Imagen de la web: PHP 8.4 con Apache.
# (PHP 8.1 ya no recibe parches de seguridad desde dic-2025 y PHP 8.2 los deja de recibir en dic-2026;
#  PHP 8.4 los recibe hasta dic-2028)
FROM php:8.4-apache

# Activa la extension mysqli, que usa tu codigo para hablar con la base de datos
RUN docker-php-ext-install mysqli

# Modulo que permite poner cabeceras de seguridad
RUN a2enmod headers

# Ajustes de PHP
COPY php-app.ini /usr/local/etc/php/conf.d/zz-app.ini

# Bloquea el acceso web a .env, .sql, docker-compose.yml, etc. y pone cabeceras de seguridad
COPY seguridad.conf /etc/apache2/conf-available/seguridad.conf
RUN a2enconf seguridad
