FROM php:8.2-fpm

# Instalar dependencias del sistema + Supervisor + Node.js 20.x
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev zip nginx supervisor gnupg \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Directorio de la app
WORKDIR /var/www/html
COPY . .

# Instalar dependencias de Laravel (PHP)
RUN composer install --no-dev --optimize-autoloader

# Instalar dependencias JS y compilar assets con Vite (Node 20.x)
RUN npm install && npm run build

# Permisos de storage y cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Configuración Nginx
RUN cat > /etc/nginx/sites-available/default <<'EOF'
server {
    listen 80;
    index index.php index.html;
    root /var/www/html/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_index index.php;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

# Configuración Supervisor (Nginx + PHP-FPM)
RUN cat > /etc/supervisor/conf.d/supervisord.conf <<'EOF'
[supervisord]
nodaemon=true

[program:php-fpm]
command=/usr/local/sbin/php-fpm --nodaemonize

[program:nginx]
command=/usr/sbin/nginx -g "daemon off;"
EOF

# Exponer puerto 80
EXPOSE 80

# Comando de arranque: correr migraciones y luego Supervisor
CMD ["sh", "-c", "php artisan migrate --force && /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf"]
