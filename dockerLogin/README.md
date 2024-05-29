# dockerを使用し、ログインページを作成する
- 参照：https://qiita.com/ucan-lab/items/56c9dc3cf2e6762672f4

- 【補足】最終的なディレクトリ構成
```
.
├── README.md (この名前にするとGitHubで見た時にHTMLに変換して表示してくれる)
├── infra (*1)
│   ├── mysql (*1)
│   │   ├── Dockerfile
│   │   └── my.cnf (*1)
│   ├── nginx (*1)
│   │   └── default.conf (*1)
│   └── php (*1)
│       ├── Dockerfile (この名前にするとファイル名の指定を省略できる)
│       └── php.ini (*1)
├── docker-compose.yml (この名前にするとファイル名の指定を省略できる)
└── src (*1)
    └── Laravelをインストールするディレクトリ

```
- (*1) 任意の名前に変更してもok

## 1.アプリケーションサーバ(app)コンテナを作る
- 【補足】ディレクトリ構成
```
.
├── infra
│   └── php
│       ├── Dockerfile
│       └── php.ini # PHPの設定ファイル
├── src # Laravelをインストールするディレクトリ
└── docker-compose.yml

```
1-1. docker-compose.ymlを作成する
```
touch docker-compose.yml
```

1-1-2. 作成した docker-compose.yml を下記の通りに編集
```yml:docker-compose.yml
version: "3.9"
services:
  app:
    build: ./infra/php
    volumes:
      - ./src:/data
``` 

1-2. ./infra/php/Dockerfile を作成する
```
mkdir -p infra/php

touch infra/php/Dockerfile
```
- mkdir -p 必要に応じて親ディレクトリも作成してくれるオプション

1-2-1. infra/php/Dockerfile を編集
```Dockerfile:infra/php/Dockerfile
FROM php:8.1-fpm-buster

ENV COMPOSER_ALLOW_SUPERUSER=1 \
  COMPOSER_HOME=/composer

COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

RUN apt-get update && \
  apt-get -y install --no-install-recommends git unzip libzip-dev libicu-dev libonig-dev && \
  apt-get clean && \
  rm -rf /var/lib/apt/lists/* && \
  docker-php-ext-install intl pdo_mysql zip bcmath

COPY ./php.ini /usr/local/etc/php/php.ini

WORKDIR /data

```

1-3. ./infra/php/php.ini を作成する
```
touch infra/php/php.ini
```

1-3-1. touch infra/php/php.ini を編集する
```ini:infra/php/php.ini
zend.exception_ignore_args = off
expose_php = on
max_execution_time = 30
max_input_vars = 1000
upload_max_filesize = 64M
post_max_size = 128M
memory_limit = 256M
error_reporting = E_ALL
display_errors = on
display_startup_errors = on
log_errors = on
error_log = /dev/stderr
default_charset = UTF-8

[Date]
date.timezone = Asia/Tokyo

[mysqlnd]
mysqlnd.collect_memory_statistics = on

[Assertion]
zend.assertions = 1

[mbstring]
mbstring.language = Japanese

```

1-4. src ディレクトリを作成する
```
mkdir src
```

1-5. build & up
```
docker compose build

docker compose up -d

docker compose ps
```

1-6. appコンテナ内ミドルウェアのバージョン確認しておく
```
docker compose exec app bash

php -v

composer -V

# インストール済みの拡張機能の一覧
php -m

exit

# コンテナの外から php コマンドを実行することもできる
docker compose exec app php -v
```

1-7. コンテナを破壊する
```
# docker-compose.yml を変更する場合は一度コンテナを破棄しておく。
docker compose down
```


## 2. ウェブサーバー(web)コンテナを作る
- nginxウェブサーバーコンテナを作成する(nginxのベースイメージをそのまま利用する)

- 【補足】ディレクトリ構成
```
.
├── infra
│   └── nginx
│       └── default.conf # nginxの設定ファイル
├── src
│  └── public # 動作確認用に作成
│       ├── index.html # HTML動作確認用
│       └── phpinfo.php # PHP動作確認用
└─── docker-compose.yml
```

2-1. docker-compose.yml へ追記する
- ポート転送の設定(今回は8080ポートにする)
- タイムゾーンの設定
```
version: "3.9"
services:
  app:
    build: ./infra/php
    volumes:
      - ./src:/data

  # 追記
  web:
    image: nginx:1.20-alpine
    ports:
      - 8080:80
    volumes:
      - ./src:/data
      - ./infra/nginx/default.conf:/etc/nginx/conf.d/default.conf
    working_dir: /data

```

2-2. infra/nginx/default.conf を作成する
```
mkdir infra/nginx

touch infra/nginx/default.conf
```
```conf:infra/nginx/default.conf
server {
    listen 80;
    server_name example.com;
    root /data/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

```

2-3. build & up
```
docker compose up -d

docker compose ps
```

2-3-1. nginxのバージョンを確認しておく
```
docker compose exec web nginx -v
```

2-4. webコンテナの確認
- webコンテナの動作確認
- HTMLとPHPが表示されるか
```
mkdir src/public
echo "Hello World" > src/public/index.html
echo "<?php phpinfo();" > src/public/phpinfo.php
```

- 