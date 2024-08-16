FROM php:8.2-fpm

# 作業ディレクトリを設定
WORKDIR /var/www

# 必要なパッケージと拡張機能をインストール
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install zip

# Composer をインストール
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 必要な PHP 拡張をインストール
RUN docker-php-ext-install pdo pdo_mysql

# デフォルトのコマンドを設定（オプション）
CMD ["php-fpm"]

