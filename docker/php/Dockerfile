FROM php:8.2-fpm

# 必要なパッケージをインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
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
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# PCOV（テストのコードカバレッジ計測用）
RUN pecl install pcov && docker-php-ext-enable pcov

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 作業ディレクトリ
WORKDIR /var/www/html

# 権限設定
RUN chown -R www-data:www-data /var/www/html

RUN ln -sf /usr/share/zoneinfo/Asia/Tokyo /etc/localtime