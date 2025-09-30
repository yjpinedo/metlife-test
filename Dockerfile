# Imagen base PHP con FPM
FROM php:8.2-fpm

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    curl \
    git \
    unzip \
    libonig-dev \
    libxml2-dev \
    zip \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath

# Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Instalar Node.js 20.x
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Copiar archivos del proyecto
WORKDIR /var/www/html
COPY . .

# Construir assets con Vite
RUN npm install && npm run build

# Configuración Nginx
RUN rm -f /etc/nginx/sites-enabled/* && \
    echo 'server {
        listen 0.0.0.0:80;
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
    }' > /etc/nginx/sites-available/laravel.conf && \
    cp /etc/nginx/sites-available/laravel.conf /etc/nginx/sites-enabled/default

# Configuración Supervisor
RUN mkdir -p /etc/supervisor/conf.d
RUN echo '[supervisord]
nodaemon=true

[program:php-fpm]
command=/usr/local/sbin/php-fpm --nodaemonize
autorestart=true
user=root

[program:nginx]
command=/usr/sbin/nginx -g "daemon off;"
autorestart=true
user=root
' > /etc/supervisor/conf.d/supervisord.conf

# Script de entrada
RUN echo '#!/bin/bash \n\
set -e \n\
echo "Ejecutando migraciones..." \n\
php artisan migrate --force || true \n\
echo "Verificando puertos abiertos..." \n\
netstat -tlnp | grep 80 || true \n\
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf \n\
' > /entrypoint.sh && chmod +x /entrypoint.sh

# Exponer el puerto
EXPOSE 80

# Comando de inicio
CMD ["/entrypoint.sh"]
