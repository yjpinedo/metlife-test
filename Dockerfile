# Imagen base PHP con FPM
FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    curl \
    git \
    unzip \
    libonig-dev \
    libxml2-dev \
    zip \
    net-tools \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath

# Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Instalar Node.js 20.x
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Copiar archivos del proyecto
WORKDIR /var/www/html
COPY . .

# Crear .env fake para que Vite no falle en build
RUN printf "APP_NAME=Laravel\n\
APP_ENV=production\n\
APP_KEY=base64:fakefakefakefakefakefakefakefake=\n\
APP_DEBUG=false\n\
APP_URL=http://localhost\n\
LOG_CHANNEL=stack\n\
DB_CONNECTION=mysql\n\
DB_HOST=127.0.0.1\n\
DB_PORT=3306\n\
DB_DATABASE=fake\n\
DB_USERNAME=fake\n\
DB_PASSWORD=fake\n" > .env

# Construir assets con Vite
RUN npm install --legacy-peer-deps && npm run build

# Configuración Nginx
RUN rm -f /etc/nginx/sites-enabled/* && \
    printf "server {\n\
        listen 0.0.0.0:80;\n\
        index index.php index.html;\n\
        root /var/www/html/public;\n\
\n\
        location / {\n\
            try_files \$uri \$uri/ /index.php?\$query_string;\n\
        }\n\
\n\
        location ~ \.php$ {\n\
            include fastcgi_params;\n\
            fastcgi_pass 127.0.0.1:9000;\n\
            fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;\n\
            fastcgi_index index.php;\n\
        }\n\
\n\
        location ~ /\.ht {\n\
            deny all;\n\
        }\n\
    }\n" > /etc/nginx/sites-available/laravel.conf && \
    cp /etc/nginx/sites-available/laravel.conf /etc/nginx/sites-enabled/default

# Configuración Supervisor
RUN mkdir -p /etc/supervisor/conf.d && \
    printf "[supervisord]\n\
nodaemon=true\n\
\n\
[program:php-fpm]\n\
command=/usr/local/sbin/php-fpm --nodaemonize\n\
autorestart=true\n\
user=root\n\
\n\
[program:nginx]\n\
command=/usr/sbin/nginx -g 'daemon off;'\n\
autorestart=true\n\
user=root\n" > /etc/supervisor/conf.d/supervisord.conf

# Script de entrada
RUN printf "#!/bin/bash \n\
set -e \n\
echo 'Eliminando .env fake (si existe)...' \n\
rm -f .env \n\
echo 'Ejecutando migraciones...' \n\
php artisan migrate --force || true \n\
echo 'Verificando puertos abiertos...' \n\
netstat -tlnp | grep 80 || true \n\
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf \n" \
> /entrypoint.sh && chmod +x /entrypoint.sh

# Exponer el puerto
EXPOSE 80

# Comando de inicio
CMD ["/entrypoint.sh"]
