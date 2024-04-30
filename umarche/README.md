# 方法

## sec01
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

## sec02
1\. Larave Breezeのインストール
```
composer require laravel/breeze --dev // Using version ^1.29 for laravel/breeze
```

2\. breeze:installコマンド実行
```
php artisan breeze:install

php artisan migrate
npm install
npm run dev
```

## sec03 日本語化
- 参照：
* https://readouble.com/laravel/10.x/ja/localization.html
* https://fadotech.com/larave10-japanese/
* https://github.com/askdkc/breezejp/tree/main/stubs/lang/ja

1\. フォルダ生成 - laravel10の場合デフォルトでフォルダが生成されなかった
```
php artisan lang:publish

mkdir lang/ja
cd lang/ja
touch auth.php pagenation.php passwords.php validation.php
```

2\. lang/enのファイルを元にjaのファイルを編集（日本語化）
```
```

5\. 確認
```
```
5\. 確認
```
```
5\. 確認
```
```
5\. 確認
```
```
5\. 確認
```
```
5\. 確認
```
```