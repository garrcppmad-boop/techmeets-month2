FROM php:8.2-fpm

# 必要なパッケージをインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    tzdata \
    dnsutils \
    iputils-ping \
 && ln -sf /usr/share/zoneinfo/Asia/Tokyo /etc/localtime \
 && echo "Asia/Tokyo" > /etc/timezone

# PHP拡張機能をインストール
# gd は JPEG / WebP も扱えるように設定してからビルドする（アップロード画像の WebP 変換に使用）
RUN docker-php-ext-configure gd --with-jpeg --with-webp \
 && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# phpredis（Laravel のキャッシュを Redis に保存するため）
RUN pecl install redis && docker-php-ext-enable redis

# PCOV（テストのコードカバレッジ計測用）
RUN pecl install pcov && docker-php-ext-enable pcov \
 && echo "pcov.directory=/var/www/html/app" >> /usr/local/etc/php/conf.d/docker-php-ext-pcov.ini

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリ
WORKDIR /var/www/html

# 権限設定
RUN chown -R www-data:www-data /var/www/html

RUN ln -sf /usr/share/zoneinfo/Asia/Tokyo /etc/localtime