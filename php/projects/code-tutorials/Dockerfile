FROM php:8.2-apache-bookworm

# Install system dependencies with security updates
RUN apt-get update && apt-get upgrade -y \
    && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo \
    pdo_mysql \
    mysqli

# Enable Apache modules
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Set proper permissions (more secure than 777)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 755 /var/www/html/assets

# Security hardening
RUN rm -rf /tmp/* /var/tmp/* \
    && find /var/log -type f -delete \
    && a2dissite 000-default.conf \
    && echo "ServerTokens Prod" >> /etc/apache2/apache2.conf \
    && echo "ServerSignature Off" >> /etc/apache2/apache2.conf

# Create non-root user for security
RUN useradd -m -u 1000 appuser && chown -R appuser:appuser /var/www/html
USER appuser

# Expose port 80
EXPOSE 80

# Start Apache as root (required for port 80)
USER root
CMD ["apache2-foreground"]
