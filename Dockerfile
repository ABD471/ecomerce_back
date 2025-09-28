FROM php:8.1-fpm

WORKDIR /var/www/html

# تثبيت الحزم المطلوبة
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    cron \
    libpq-dev \
    zip \
    unzip \
    git \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip \
        mbstring

# تثبيت Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# نسخ ملفات Composer أولًا
COPY ./src/composer.json ./src/composer.lock ./
RUN composer install --no-interaction --optimize-autoloader

# نسخ ملفات المشروع
COPY ./src ./

# نسخ إعدادات Nginx
COPY ./nginx/nginx.conf /etc/nginx/conf.d/default.conf

# نسخ مهمة cron
COPY cronjob /etc/cron.d/delete-unverified
RUN chmod 0644 /etc/cron.d/delete-unverified
RUN touch /var/log/cron.log

# نسخ إعدادات supervisord
COPY supervisord.conf /etc/supervisord.conf

# فتح المنفذ 80
EXPOSE 80

# تشغيل الخدمات
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
