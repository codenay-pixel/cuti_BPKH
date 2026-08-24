# ===== Stage 1: Build frontend assets (Vite) =====
FROM node:20-alpine AS node-build
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY resources/ resources/
COPY vite.config.js ./
COPY public/ public/
RUN npm run build

# ===== Stage 2: PHP application =====
FROM richarvey/nginx-php-fpm:3.1.6

COPY . /var/www/html
COPY --from=node-build /app/public/build /var/www/html/public/build

ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/start.sh"]
