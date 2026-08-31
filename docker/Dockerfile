FROM php:8.2-fpm-alpine

LABEL maintainer="Beppe Info <beppe.info@protonmail.com>"
LABEL org.opencontainers.image.source="https://github.com/BeppeInfo/Portfolio-Page"

# Install Nginx + PHP extensions
RUN apk add --no-cache \
      nginx \
      icu-dev \
      oniguruma-dev \
      zlib-dev \
      libwebp-dev \
      libjpeg-turbo-dev \
      libpng-dev \
      pkgconfig \
    && docker-php-ext-install \
       intl \
       mbstring \
       gd \
    && docker-php-ext-enable intl \
    && apk del icu-dev oniguruma-dev zlib-dev libwebp-dev libjpeg-turbo-dev libpng-dev pkgconfig \
    && apk add --no-cache \
       libpng \
       libjpeg-turbo \
       libwebp \
       zlib \
       icu-libs \
    && rm -rf /var/cache/apk/* /tmp/* /var/tmp/* \
    && mkdir -p /var/run/nginx /var/log/nginx /run

# Copy application
COPY . /var/www/html

# Copy Nginx configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/server.conf /etc/nginx/conf.d/server.conf

# PHP-FPM config
COPY docker/www.conf /usr/local/etc/php-fpm.d/docker.conf

# Copy startup script
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh && \
    mkdir -p /var/run && \
    chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
