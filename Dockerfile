# Imagen base con PHP y Apache
FROM php:8.2-apache

# Instalar dependencias necesarias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    curl

# Instalar extensiones de PHP requeridas por Laravel y maatwebsite/excel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo pdo_mysql zip gd

# Habilitar módulo de reescritura de Apache
RUN a2enmod rewrite

# Configurar Apache para usar el directorio /public como raíz del sitio
RUN echo 'DocumentRoot /var/www/html/public' > /etc/apache2/conf-available/document-root.conf && \
    echo '<Directory /var/www/html/public>' >> /etc/apache2/conf-available/document-root.conf && \
    echo '    Options Indexes FollowSymLinks' >> /etc/apache2/conf-available/document-root.conf && \
    echo '    AllowOverride All' >> /etc/apache2/conf-available/document-root.conf && \
    echo '    Require all granted' >> /etc/apache2/conf-available/document-root.conf && \
    echo '    DirectoryIndex index.php' >> /etc/apache2/conf-available/document-root.conf && \
    echo '</Directory>' >> /etc/apache2/conf-available/document-root.conf && \
    a2enconf document-root && \
    a2dissite 000-default.conf && \
    echo '<VirtualHost *:80>' > /etc/apache2/sites-available/000-rinconcito.conf && \
    echo '    ServerAdmin webmaster@localhost' >> /etc/apache2/sites-available/000-rinconcito.conf && \
    echo '    DocumentRoot /var/www/html/public' >> /etc/apache2/sites-available/000-rinconcito.conf && \
    echo '    <Directory /var/www/html/public>' >> /etc/apache2/sites-available/000-rinconcito.conf && \
    echo '        Options Indexes FollowSymLinks' >> /etc/apache2/sites-available/000-rinconcito.conf && \
    echo '        AllowOverride All' >> /etc/apache2/sites-available/000-rinconcito.conf && \
    echo '        Require all granted' >> /etc/apache2/sites-available/000-rinconcito.conf && \
    echo '    </Directory>' >> /etc/apache2/sites-available/000-rinconcito.conf && \
    echo '    ErrorLog ${APACHE_LOG_DIR}/error.log' >> /etc/apache2/sites-available/000-rinconcito.conf && \
    echo '    CustomLog ${APACHE_LOG_DIR}/access.log combined' >> /etc/apache2/sites-available/000-rinconcito.conf && \
    echo '</VirtualHost>' >> /etc/apache2/sites-available/000-rinconcito.conf && \
    a2ensite 000-rinconcito.conf

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar Composer desde la imagen oficial
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copiar archivos del proyecto (opcional, si no lo haces en Render directo)
# COPY . .

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

# Ajustar permisos necesarios para Laravel
# RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Puerto que expone Apache
EXPOSE 80

# Comando de inicio
CMD ["apache2-foreground"]
