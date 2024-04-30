# 方法

1\. プロジェクト作成
```
$ composer create-project laravel/laravel umarche "10.*" --prefer-dist
$ cd umarche
```

2\. composer update

3\. phpMyAdminでデータベース・ユーザアカウントを手動で作成
- データベース名：laravel_umarche
- ユーザアカウント名（laravel_umarche > 権限 > 新規作成「ユーザアカウントを追加する」）：umarche

4\. .envファイルを編集
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=laravel_umarche
DB_USERNAME=umarche
DB_PASSWORD=password123
```

5\. データベースと接続
```
php artisan migrate
```

6\. 初期設定（タイムゾーン、言語設定）
```php:laravel_api/config/app.php
'timezone' => 'Asia/Tokyo',
'locale' => 'ja',
```

7\. デバックバーのインストール（DBとの接続内容やHTTPの中身がわかったりして便利）
```
composer require barryvdh/laravel-debugbar
```
--- 

8\. デバックモードの確認
```env
APP_DEBUG=true // 開発時はtrue、本番環境時はfalseにする
```

9\. Larave Breezeのインストール
```
composer require laravel/breeze --dev // Using version ^1.29 for laravel/breeze
```

10\. breeze:installコマンド実行
```
php artisan breeze:install

php artisan migrate
npm install
npm run dev
```

4\. 
4-1\. 
```
```

4-2\. 
```
```

5\. 確認
```
```