FROM php:8.2-apache

# 安装 PHP 扩展
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 启用 Apache 模块
RUN a2enmod rewrite

# 配置 Apache
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# 设置工作目录
WORKDIR /var/www/html

# 设置时区
RUN ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime

# 暴露端口
EXPOSE 80

CMD ["apache2-foreground"]
