FROM php:8.2-fpm-alpine

LABEL maintainer="LeonardoJC <you@example.com>"
LABEL org.opencontainers.image.source="https://github.com/leonardojc/Portfolio-Page"

# Install Nginx + PHP extensions
RUN apk add --no-cache \
      nginx \
      icu-data-full \
      icu-libs \
    && docker-php-ext-install \
       intl \
       mbstring \
       gd \
    && rm -rf /var/cache/apk/* \
    && mkdir -p /var/run/nginx /var/log/nginx

# Copy application
COPY . /var/www/html

# Copy Nginx config
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# PHP-FPM config
COPY docker/www.conf /usr/local/etc/php-fpm.d/docker.conf
RUN mkdir -p /var/run && \
    chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 80

# Start both Nginx and PHP-FPM
CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
