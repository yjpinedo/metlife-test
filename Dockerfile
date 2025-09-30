# --------------------------
# Dockerfile para Laravel 12 en Railway
# --------------------------

# Imagen base PHP con FPM
FROM php:8.2-fpm

# Instalar dependencias del sistema + Node.js 20 + Supervisor + gettext-base (para envsubst)
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev zip nginx supervisor gnupg net-tools gettext-base \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Directorio de la app
WORKDIR /var/www/html
COPY . .

# Crear .env fake temporal para Vite
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

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader

# Instalar dependencias JS y compilar assets con Vite
RUN npm install --legacy-peer-deps && npm run build \
    && ls -la public/build \
    && test -f public/build/manifest.json || (echo '❌ ERROR: No se generó public/build/manifest.json' && exit 1)

# Permisos de storage y cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Configuración Nginx
RUN rm -f /etc/nginx/sites-enabled/* && \
    printf "server {\n\
        listen \${PORT};\n\
        index index.php index.html;\n\
        root /var/www/html/public;\n\
\n\
        location / {\n\
            try_files \$uri \$uri/ /index.php?\$query_string;\n\
        }\n\
\n\
        location ~ \.php\$ {\n\
            include fastcgi_params;\n\
            fastcgi_pass 127.0.0.1:9000;\n\
            fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;\n\
            fastcgi_param HTTPS on;\n\
            fastcgi_param HTTP_X_FORWARDED_PROTO \$http_x_forwarded_proto;\n\
            fastcgi_index index.php;\n\
            fastcgi_buffer_size 32k;\n\
            fastcgi_buffers 4 32k;\n\
        }\n\
\n\
        location ~ /\.ht {\n\
            deny all;\n\
        }\n\
    }\n" > /etc/nginx/conf.d/default.conf

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

# Entrypoint
RUN printf "#!/bin/bash\n\
set -e\n\
\n\
# Configurar puerto dinámico Nginx\n\
echo 'Configurando Nginx con el puerto \${PORT}...'\n\
envsubst '\$PORT' < /etc/nginx/conf.d/default.conf > /etc/nginx/conf.d/default.conf.tmp\n\
mv /etc/nginx/conf.d/default.conf.tmp /etc/nginx/conf.d/default.conf\n\
\n\
# Eliminar .env temporal\n\
rm -f .env\n\
\n\
# Ejecutar migraciones\n\
php artisan migrate --force || true\n\
\n\
# Forzar HTTPS en Laravel (para sesiones seguras)\n\
php artisan config:clear\n\
php artisan cache:clear\n\
php artisan route:clear\n\
php artisan view:clear\n\
\n\
# Iniciar Supervisor (PHP-FPM + Nginx)\n\
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf\n" \
> /entrypoint.sh && chmod +x /entrypoint.sh

# Exponer puerto dinámico (Railway lo define con $PORT)
EXPOSE 8080

# Comando de inicio
CMD ["/entrypoint.sh"]
