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

2\. lang/enのファイルを元にjaディレクトリ4つのファイルを編集（日本語化）
* 参照：https://github.com/askdkc/breezejp/tree/main/stubs/lang/ja

3\.lang/jaに別途jsonファイル作成 
```
cd lang/ja
touch ja.json
```

4\. jsonファイルを編集
```json:lang/ja/ja.json
{"Whoops! Something went wrong.": "すみません、サーバーで何か問題が発生しました。"}
// あってもなくてもいい
```

5\. http://127.0.0.1:8000/registerにてバリデーションチェック＋ユーザアカウント作成
```
ユーザ名：テスト
メール：test@test.com
パスワード:password123
```

## sec04 component 準備
1\. routesを編集
```php:routes/web.php
use App\Http\Controllers\ComponentTestController;

Route::get('/component-test1', [ComponentTestController::class, 'showComponent1']);
Route::get('/component-test2', [ComponentTestController::class, 'showComponent2']);

```
2\. コントローラの作成
```
php artisan make:controller ComponentTestController
```
- 作成されているか確認(Http > Controllers > ComponentTestController.php)

3\. 作成したコントローラの編集
```php:Http/Controllers/ComponentTestController.php
class ComponentTestController extends Controller
{
    //
    public function showComponent1(){
        return view('tests.component-test1');
    }
    public function showComponent2(){
        return view('tests.component-test2');
    }
}
```
4\. testsフォルダ作成
```
mkdir views/tests
cd views/tests
touch component-test1.blade.php component-test2.blade.php 
```
5\. 作成したファイルの編集
```php:views/tests/component-test1.blade.php 
コンポーネントテスト１
```
```php:views/tests/component-test2.blade.php 
コンポーネントテスト２
```

6\. ローカルサーバで確認(http://127.0.0.1:8000/component-test1)
```
php artisan serve
```


## sec05 slotの使用方法
1\. フォルダ・ファイル作成
```
mkdir resources/views/components/tests
cd resources/views/components/tests
touch app.blade.php
```

2\. コピペ
- 下記ファイルの内容をコピーする
- resources/views/layouts/guest.blade.php
```php:resources/views/components/tests
// guest.blade.phpのコードをペースト
```

3\. コンポーネントの表示内容を編集
```php:resources/views/tests/component-test1.blade.php
<x-tests.app>
コンポーネントテスト１
</x-tests.app>
```

4\. ローカルサーバを立ち上げて確認する
```
php artisan serve
```
- http://127.0.0.1:8000/component-test1
- 表示デザインが変更されている。
- DevToolsで確認するとheadタグでscript・linkタグが読み込まれていることがわかる。

5\. component-test2にも同じ内容を反映しておく
```php:resources/views/tests/component-test2.blade.php
<x-tests.app>
コンポーネントテスト２
</x-tests.app>
```

## sec05 名前付きslotの使用方法
1\. Bladeコンポーネント側でコンテンツを表示させたい変数名を用意しておく
```php:resources/views/components/tests/app.blade.php
<body>
    <header>
        {{ $header }}
    </header>
...
</body>
```

2\. コンテンツを渡す側のファイルに変更を加える
```php:resources/views/tests/component-test1.blade.php
<x-tests.app>
    <x-slot name="header">ヘッダー１</x-slot>
    コンポーネントテスト１
</x-tests.app>
```

```php:umarche/resources/views/tests/component-test2.blade.php
<x-tests.app>
    <x-slot name="header">ヘッダー２</x-slot>
    コンポーネントテスト２
</x-tests.app>
```

3\. ローカルサーバを立ち上げて確認する
```
php artisan serve
```
- http://127.0.0.1:8000/component-test1

## sec06 Componentのパターン - データの受け渡し方法：属性
1\. Bladeコンポーネント側のファイルを1つ作成し
コンテンツを表示させたい変数名を用意しておく
```
touch resources/views/components/tests/card.blade.php
```

```php:resources/views/components/tests/card.blade.php
<div class="border-2 shadow-md w-1/4 p-2">
    <div>{{ $title }}</div>
    <div>画像</div>
    <div>{{ $content }}</div>
</div>
``` 

2\. コンテンツを渡す側のファイルに変更を加える
```php:resources/views/tests/component-test1.blade.php
<x-tests.app>
    <x-slot name="header">ヘッダー１</x-slot>
    コンポーネントテスト１
    
    <x-tests.card title="タイトル" content="本文" />
</x-tests.app>
```

3\. ローカルサーバで確認(http://127.0.0.1:8000/component-test1)
```
php artisan serve
```

1\. 
```
```
2\. 
```
```
2\. 
```
```
2\. 
```
```
2\. 
```
```
2\. 
```
```
2\. 
```
```