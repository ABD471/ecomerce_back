FROM php:8.1-fpm

WORKDIR /var/www/html

# تثبيت الحزم المطلوبة + cron
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    libpq-dev \
    zip \
    unzip \
    git \
    libzip-dev \
    libonig-dev \
    cron \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip \
        mbstring

# تثبيت Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# نسخ ملفات Composer أولًا
COPY ./src/composer.json ./src/composer.lock ./

# تثبيت الاعتمادات
RUN composer install --no-interaction --optimize-autoloader

# نسخ باقي ملفات المشروع
COPY ./src ./
COPY ./nginx/nginx.conf /etc/nginx/conf.d/default.conf
# نسخ ملف المهام المجدولة
COPY cronjob /etc/cron.d/delete-unverified

# إعطاء صلاحيات للملف المجدول
RUN chmod 0644 /etc/cron.d/delete-unverified 
# إنشاء ملف سجل للـ cron
RUN touch /var/log/cron.log

# تشغيل cron و PHP-FPM معًا
COPY supervisord.conf /etc/supervisord.conf



EXPOSE 80

CMD ["supervisord", "-c", "/etc/supervisord.conf"]


