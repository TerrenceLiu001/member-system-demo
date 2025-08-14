# 1. 指定基底映像 (Base Image)
FROM php:8.3-fpm-alpine

# 2. 安裝必要系統套件 (System dependencies)
RUN apk add --no-cache \
    nano \
    git \
    openssh-client \
    composer \
    php83-session \
    php83-fileinfo \
    php83-tokenizer \
    php83-dom \
    php83-pdo_mysql

# 3. 將 pdo_mysql 載入 usr/local/lib/php 中
RUN ln -sf /usr/lib/php83/modules/pdo_mysql.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/pdo_mysql.so
RUN echo "extension=pdo_mysql" > /usr/local/etc/php/conf.d/docker-php-pdo_mysql-ext.ini 

# 4. 設定工作目錄 (Working directory)
WORKDIR /var/www/html

# 5. 複製 composer 設定檔，利用快取避免每次都重新安裝依賴
COPY composer.json composer.lock ./

# 6. 複製專案檔案 (Copy project files)
COPY . .

# 7. 安裝專案依賴 (Install dependencies)
RUN composer install --no-dev --optimize-autoloader 

# 8. 設定檔案權限 (Permissions)
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# 9. 曝露容器端口 (Expose ports)
EXPOSE 9000

# 10. 容器啟動命令 (Entrypoint / CMD)
CMD ["php-fpm"]




