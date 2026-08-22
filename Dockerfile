FROM php:8.2-apache

# Enable mysqli extension (needed since db.php uses mysqli)
RUN docker-php-ext-install mysqli

# Copy all project files into Apache's web root
COPY . /var/www/html/

# Keep the previous project-prefixed URL working in local Docker deployments.
RUN ln -s /var/www/html /var/www/html/AguaHeart

# Apache listens on port 80 by default
EXPOSE 80