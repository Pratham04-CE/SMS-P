FROM php:8.2-apache

# MySQLi aur extension optimization
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Poora code render ke root directory me copy karein
COPY . /var/www/html/

# Server par main entry point index1.php set karein
RUN echo "DirectoryIndex index1.php" >> /etc/apache2/apache2.conf