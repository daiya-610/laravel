# 使用するベースイメージ（PHP 8.2とComposerがインストールされたもの）
FROM php:8.2-fpm

# 作業ディレクトリを設定
WORKDIR /var/www

# Composerのインストール
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# 必要なパッケージをインストール
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    unzip \
    zip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo_mysql

# nodeとnpmのインストール
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

# Permissions修正（エラー回避のため）
RUN chown -R www-data:www-data /var/www

# ポート番号を指定
EXPOSE 80
