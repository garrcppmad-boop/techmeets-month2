# syntax=docker/dockerfile:1
#
# 本番用の最適化 Dockerfile（マルチステージビルド）
#   ステージ1 vendor : Composer で PHP の依存パッケージをインストール（--no-dev）
#   ステージ2 assets : Node.js で CSS/JS をビルド（npm run build）
#   ステージ3 runtime: 実行に必要なものだけをコピーした最終イメージ
#
# ローカル開発用は docker/php/Dockerfile（docker-compose.yml から使用）
#
#   ビルド: docker build -t laravel-app:prod .

ARG PHP_VERSION=8.2

# ---------------------------------------------------------------
# ステージ1: PHP の依存パッケージ（Composer）
# ---------------------------------------------------------------
FROM php:${PHP_VERSION}-cli-alpine AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# composer.json / composer.lock だけ先にコピーしてインストール
# → ソースコードを変更しても、依存が変わらなければこの層はキャッシュが効く
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --no-interaction \
        --no-progress \
        --prefer-dist

# アプリのコードをコピーして、最適化したオートローダーを生成
# （composer.json のスクリプトで、AWS SDK の未使用サービス（S3 等以外）の削除と
#   Laravel のパッケージ検出 package:discover も実行される）
COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-interaction

# ---------------------------------------------------------------
# ステージ2: フロントエンドのビルド（Vite）
# ---------------------------------------------------------------
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
# Cypress 本体（数百MB）のダウンロードはビルドに不要なのでスキップ
RUN CYPRESS_INSTALL_BINARY=0 npm ci --no-audit --no-fund

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
# Tailwind が使用クラスを探す対象（tailwind.config.js の content）に Laravel のページネーションの
# テンプレートも含まれるため、vendor ステージからコピーしておく
COPY --from=vendor /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views \
     ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

# ---------------------------------------------------------------
# ステージ3: 実行用イメージ（Alpine Linux + PHP-FPM）
# ---------------------------------------------------------------
FROM php:${PHP_VERSION}-fpm-alpine AS runtime

# PHP 拡張のインストール
#   pdo_mysql: MySQL / RDS への接続
#   opcache  : PHP のコンパイル結果をキャッシュして高速化
#   gd       : アップロード画像の WebP 変換（JPEG / PNG / WebP を扱えるように設定）
#   redis    : Laravel のキャッシュを Redis に保存（phpredis）
# fcgi はヘルスチェック（PHP-FPM への ping）に使う
# apk upgrade: ベースイメージ作成後に修正版が出た OS パッケージの脆弱性を取り込む（Trivy で検出）
# ビルドにだけ必要なパッケージ（.build-deps）は最後に削除してイメージを小さく保つ
RUN apk upgrade --no-cache \
 && apk add --no-cache fcgi libpng libjpeg-turbo libwebp \
 && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS libpng-dev libjpeg-turbo-dev libwebp-dev \
 && docker-php-ext-configure gd --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql opcache gd \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apk del .build-deps \
 && rm -rf /tmp/* /usr/src/php*

# 本番用の PHP 設定
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
    } > "$PHP_INI_DIR/conf.d/opcache-production.ini" \
 # ヘルスチェック用に PHP-FPM の ping エンドポイントを有効化
 && { \
        echo '[www]'; \
        echo 'ping.path = /ping'; \
    } > /usr/local/etc/php-fpm.d/zz-healthcheck.conf

WORKDIR /var/www/html

# 前のステージから必要なものだけをコピー（所有者は非rootユーザー www-data）
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=vendor /app/vendor ./vendor
COPY --chown=www-data:www-data --from=vendor /app/bootstrap/cache ./bootstrap/cache
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build

# Laravel が書き込むディレクトリを用意
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
 && chown -R www-data:www-data storage bootstrap/cache

# 非rootユーザーで実行
USER www-data

EXPOSE 9000

# PHP-FPM が応答しているかを30秒ごとに確認（/ping に "pong" が返れば正常）
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET \
        cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -q pong || exit 1

CMD ["php-fpm"]
