# Usamos una imagen base oficial de PHP con Apache
FROM php:8.2-apache

# Instalar dependencias del sistema necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    zip \
    curl \
    npm \
    nodejs \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    pkg-config \
    build-essential \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql intl xml mbstring

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Configurar Apache para permitir .htaccess y acceso al directorio /var/www/html
RUN echo '<Directory /var/www/html>' > /etc/apache2/conf-available/allow-override.conf && \
    echo '    Options Indexes FollowSymLinks' >> /etc/apache2/conf-available/allow-override.conf && \
    echo '    AllowOverride All' >> /etc/apache2/conf-available/allow-override.conf && \
    echo '    Require all granted' >> /etc/apache2/conf-available/allow-override.conf && \
    echo '</Directory>' >> /etc/apache2/conf-available/allow-override.conf && \
    a2enconf allow-override

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto al contenedor
COPY . /var/www/html

# Instalar dependencias PHP con Composer
RUN composer install --no-dev --optimize-autoloader

# Instalar dependencias Node.js y construir frontend
RUN npm install
RUN npm run build

# Cambiar permisos para almacenamiento y cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer puerto 80
EXPOSE 80

# Comando para iniciar Apache en primer plano
CMD ["apache2-foreground"]
