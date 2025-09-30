FROM php:8.2-fpm

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev zip nginx supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Directorio de la app
WORKDIR /var/www/html
COPY . .

# Instalar dependencias Laravel
RUN composer install --no-dev --optimize-autoloader

# Permisos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Configuración Nginx
RUN echo 'server { \
    listen 80; \
    index index.php index.html; \
    root /var/www/html/public; \
    location / { try_files $uri $uri/ /index.php?$query_string; } \
    location ~ \.php$ { include fastcgi_params; fastcgi_pass unix:/run/php/php-fpm.sock; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; fastcgi_index index.php; } \
    location ~ /\.ht { deny all; } \
}' > /etc/nginx/sites-available/default

# Configuración Supervisor
RUN mkdir -p /etc/supervisor/conf.d
RUN echo '[supervisord] \
nodaemon=true \
\n\
[program:php-fpm] \
command=/usr/local/sbin/php-fpm --nodaemonize \
\n\
[program:nginx] \
command=/usr/sbin/nginx -g "daemon off;" \
' > /etc/supervisor/conf.d/supervisord.conf

# Exponer puerto 80
EXPOSE 80

# Comando de arranque
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
