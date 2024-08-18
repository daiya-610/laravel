# DockReact

Dockerを使用してLaravel環境をセットアップします。
以下の手順は、Docker環境内でLaravelアプリケーションをセットアップし、実行するためのガイドです。

1. Dockerfile, docker-compose.ymlファイル作成
```
touch Dockerfile
touch docker-compose.yml
```

```:Dockerfile
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

```

```:docker-compose.yml
version: '3.8'

services:
  # Laravel用のPHPコンテナ
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: laravel_app
    volumes:
      - ./laravel:/var/www
    ports:
      - "8000:80"
    networks:
      - app-network
    depends_on:
      - db

  # MySQLコンテナ
  db:
    image: mysql:8.0
    container_name: mysql_db
    environment:
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_DATABASE: laravel_db
      MYSQL_USER: user
      MYSQL_PASSWORD: user_password
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - app-network

networks:
  app-network:

volumes:
  db_data:

```

2. Dockerコンテナをビルド
```
docker-compose up -d --build
```
Dockerfileで定義されたDockerイメージをビルドします。
LaravelアプリケーションとMySQLコンテナを作成し、起動します。

3. Laravelのインストールと環境設定
A. Laravelをインストール
```
docker exec -it laravel_app composer create-project --prefer-dist laravel/laravel:^10.0 .
```

B. アプリケーションキーの生成
Laravelのアプリケーションキーを生成します:

```
docker exec -it laravel_app php artisan key:generate
```

4. 環境設定の更新
A. .env ファイルの更新
.envファイルが正しく設定されていることを確認します。以下はその例です:

```:env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql_db
DB_PORT=3306
DB_DATABASE=laravel_app
DB_USERNAME=user
DB_PASSWORD=user_password
```

5. データベースのマイグレーション
初期データベーススキーマをセットアップするために、データベースのマイグレーションを実行します:

```
docker exec -it laravel_app php artisan migrate
```

6. Laravel開発サーバーの起動
Dockerコンテナ内でLaravel開発サーバーを起動します:

```
docker-compose exec app php artisan serve --host=0.0.0.0 --port=80
```

7. アプリケーションへのアクセス
最後に、Webブラウザを開き、以下のURLにアクセスします:

http://localhost:8000
これで、Dockerコンテナ内で実行されているLaravelアプリケーションが表示されます。

