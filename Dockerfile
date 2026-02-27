# 使用带 Nginx + PHP-FPM 的 7.4 镜像
FROM richarvey/nginx-php-fpm:php7.4

WORKDIR /var/www/html

# 复制代码
COPY . /var/www/html

# 安装 Composer 依赖并优化
RUN composer install --no-dev --optimize-autoloader \
    && cp .env.example .env || true \
    && php artisan key:generate || true \
    && chown -R www-data:www-data storage bootstrap/cache

# 指定 Laravel 的 public 目录为 Web 根
ENV WEBROOT=/var/www/html/public

EXPOSE 80

CMD ["/start.sh"]
