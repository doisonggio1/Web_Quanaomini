# Sử dụng image HTTPD chính thức
FROM php:7.3-apache

# Cài đặt các thư viện cần thiết và extension PHP (mysqli, pdo, pdo_mysql, bcmath)
RUN apt-get update && apt-get install -y \
    && docker-php-ext-install mysqli pdo pdo_mysql bcmath

# Kích hoạt mod_rewrite cho Apache
RUN a2enmod rewrite

# Thay đổi Apache Listen Port từ 80 -> 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf

# Thêm cấu hình để cho phép sử dụng .htaccess trong thư mục gốc và public
RUN echo '<Directory /var/www/html>\n    AllowOverride All\n</Directory>\n<Directory /var/www/html/public>\n    AllowOverride All\n</Directory>' >> /etc/apache2/sites-available/000-default.conf

# Thiết lập thư mục lưu session
RUN mkdir -p /var/www/html/sessions && \
    chown -R www-data:www-data /var/www/html/sessions && \
    chmod -R 0700 /var/www/html/sessions

# Thiết lập quyền cho thư mục upload
RUN mkdir -p /usr/local/apache2/htdocs/upload && \
    chown -R www-data:www-data /usr/local/apache2/htdocs/upload && \
    chmod -R 755 /usr/local/apache2/htdocs/upload

# Khởi động Apache
CMD ["apache2-foreground"]
