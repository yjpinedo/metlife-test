# Imagen base PHP con FPM
FROM php:8.2-fpm

# Instalar dependencias del sistema + Supervisor + Node.js 20.x
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev zip nginx supervisor gnupg net-tools \
    python3 make g++ \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Directorio de la app
WORKDIR /var/www/html
COPY . .

# Crear .env fake para que Vite no falle en build
RUN printf "APP_NAME=Laravel\n\
APP_ENV=production\n\
APP_KEY=base64:fakefakefakefakefakefakefakefake=\n\
APP_DEBUG=false\n\
APP_URL=https://metlife-test.onrender.com\n\
ASSET_URL=https://metlife-test.onrender.com\n\
LOG_CHANNEL=stack\n\
DB_CONNECTION=mysql\n\
DB_HOST=127.0.0.1\n\
DB_PORT=3306\n\
DB_DATABASE=fake\n\
DB_USERNAME=fake\n\
DB_PASSWORD=fake\n" > .env

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader

# Instalar dependencias JS y compilar assets con Vite
RUN npm install --legacy-peer-deps \
    && npm run build \
    && ls -la public/build \
    && test -f public/build/manifest.json || (echo '❌ ERROR: No se generó public/build/manifest.json' && exit 1)

# Permisos de storage y cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Configuración Nginx (puerto dinámico $PORT de Render)
RUN rm -f /etc/nginx/sites-enabled/* && \
    printf "server {\n\
        listen 0.0.0.0:PORT_REPLACE;\n\
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
: \${PORT:=10000} \n\
echo \"🔧 Usando puerto \$PORT\" \n\
sed -i \"s/PORT_REPLACE/\$PORT/\" /etc/nginx/sites-available/laravel.conf \n\
rm -f .env \n\
echo '📦 Ejecutando migraciones...' \n\
php artisan migrate --force || true \n\
echo '🌐 Verificando puertos abiertos...' \n\
netstat -tlnp | grep \$PORT || true \n\
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf \n" \
> /entrypoint.sh && chmod +x /entrypoint.sh

# Exponer puerto (Render lo reemplaza con $PORT)
EXPOSE 10000

# Comando de inicio
CMD ["/entrypoint.sh"]
