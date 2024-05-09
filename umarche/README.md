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

## sec06 Componentのパターン - データの受け渡し方法：変数
1\. 変数を受け渡すためのコントローラを編集
```php:app/Http/Controllers/ComponentTestController.php
public function showComponent1(){
        $message = 'メッセージ'; // 追加
        return view('tests.component-test1',
    compact('message')); // 編集：セミコロンと変数名を追加
    }
```
2\. Bladeコンポーネント側でコンテンツを表示させたい変数名を追加する
```php:resources/views/components/tests/card.blade.php
<div class="border-2 shadow-md w-1/4 p-2">
    <div>{{ $title }}</div>
    <div>画像</div>
    <div>{{ $content }}</div>
    <div>{{ $message }}</div> // 追加
</div>
```

3\. コンテンツを渡す側のファイルに変更を加える
```php:resources/views/tests/component-test1.blade.php
<x-tests.app>
    <x-slot name="header">ヘッダー１</x-slot>
    コンポーネントテスト１

    <x-tests.card title="タイトル" content="本文" :message="$message" /> // 「:」を付けないと属性と認識されそのまま表示されてしまう。コントローラーにて変数設定:app/Http/Controllers/ComponentTestController.php
</x-tests.app>
```

## sec07 Componentのパターン - 初期値の設定（@props）
- 変数名が定義されてない場合エラーとなる：エラーメッセージ「Undefined variable $content」
- 予め初期値を設定しておくことでエラーを防ぐ

1\. コンテンツを渡す側のファイルに変更を加える
```php:resources/views/tests/component-test1.blade.php
<x-tests.app>
    <x-slot name="header">ヘッダー１</x-slot>
    コンポーネントテスト１

    <x-tests.card title="タイトル" content="本文" :message="$message" />
    <x-tests.card title="タイトル2" /> // 追加
    // <x-tests.card title="タイトル2" content="本文2" :message="$message"/> ←のように「title, content, messageを定義しないとエラーが返ってくる。」
</x-tests.app>
```

2\. ローカルサーバで確認(http://127.0.0.1:8000/component-test1)
```
php artisan serve
```
- エラーメッセージが返ってくる：Undefined variable $content

3\. Bladeコンポーネント側のファイルに初期値（@props）を設定する
```php:resources/views/components/tests/card.blade.php
@props([
    'title',
    'message' => '初期値です。',
    'content' => '本文初期値です。'
]) // 追加：propsは連想配列の形式で書く。 初期値が特に必要ない場合はプロパティ名のみでOK
<div class="border-2 shadow-md w-1/4 p-2">
    <div>{{ $title }}</div>
    <div>画像</div>
    <div>{{ $content }}</div>
    <div>{{ $message }}</div>
</div>
@props(['message' => '初期値です。']) 
<div class="border-2 shadow-md w-1/4 p-2">
    <div>{{ $title }}</div>
    <div>画像</div>
    <div>{{ $content }}</div>
    <div>{{ $message }}</div>
</div>
```

4\. 初期値（@props）を設定したのでコンテンツを渡す側のファイルに変更を加える
```php:resources/views/tests/component-test1.blade.php
<x-tests.app>
    <x-slot name="header">ヘッダー１</x-slot>
    コンポーネントテスト１

    <x-tests.card title="タイトル" content="本文" :message="$message" />
    <x-tests.card title="タイトル2" />
    <!-- <x-tests.card title="タイトル2" content="本文2" :message="$message"/>  // 初期値（@props）を設定しない場合は左記のように全て定義しないとエラーになる。--> 
</x-tests.app>
```

5\. ローカルサーバで確認(http://127.0.0.1:8000/component-test1)
```
php artisan serve
```
- 初期値を設定したため、エラーなく表示されることを確認。

## sec08 Componentのパターン - 属性バッグ（$attribute）
- 属性バッグ：CSSのクラスを渡す際に使用される機能
1\. コンテンツを渡す側のファイルに変更を加える
```php:resources/views/tests/component-test1.blade.php
<x-tests.app>
    <x-slot name="header">ヘッダー１</x-slot>
    コンポーネントテスト１

    <x-tests.card title="タイトル" content="本文" :message="$message" />
    <x-tests.card title="タイトル2" />
    <x-tests.card title="CSSを変更したい" class="bg-red-300" /> // 追加
</x-tests.app>
```
- この状態だとこのクラスは有効にならない。
- Bladeコンポーネント側のファイルに新しく変数を作る必要がある。

2\. Bladeコンポーネント側のファイルに属性バッグ（$attribute）を追加する
```php:resources/views/components/tests/card.blade.php
@props([
    'title',
    'message' => '初期値です。',
    'content' => '本文初期値です。'
])

<div {{ $attributes->merge([
    'class' => 'border-2 shadow-md w-1/4 p-2'
]) }} > // 追加 merge：コンテンツを渡す側のファイルと表示させたい側のファイルで指定しているclass名が干渉しうまく表示されないためmergeを使用
    <div>{{ $title }}</div>
    <div>画像</div>
    <div>{{ $content }}</div>
    <div>{{ $message }}</div>
</div>
```

3\. tailwindd.cssを読み込ませる
```json:package.json
"scripts": {
    "dev": "vite",
    "build": "vite build",
    "watch": "webpack --watch" // 追加
},
```

```
npm install
npm run watch
```

4\. ローカルサーバで確認(http://127.0.0.1:8000/component-test1)
```
php artisan serve
```
- 最後のブロックだけ背景色が背景色が付いている事を確認

## sec09 Componentのパターン - クラスベース
1\. クラスの作成
- TestClassBaseというClass名を作詞
```
php artisan make:component TestClassBase
```
- 下記メッセージが表示されていればOK
- Component [app/View/Components/TestClassBase.php] created successfully.  

*** 補足 ***
```php:app/View/Components/TestClassBase.php
class TestClassBase extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct() // ここで変数などを設定する
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */ // viewファイル側に渡す処理
    public function render(): View|Closure|string
    {
        return view('components.test-class-base');
    }
}

```

2\. 作成されたBladeコンポーネント側のファイルをtestsフォルダの中に移動させる。
```
 mv resources/views/components/test-class-base.blade.php resources/views/components/tests
```

3\. ファイルの移動したので先ほど作成したクラスのファイルも編集する
```php:app/View/Components/TestClassBase.php
public function render(): View|Closure|string
    {
        return view('components.tests.test-class-base');
    }
```

4\. Bladeコンポーネント側のファイルを編集する
```php:resources/views/components/tests/test-class-base.blade.php
<div>
    クラスベースのコンポーネントです。
    使用する場合は
    App/View/Components内のクラスを指定する。
    クラス名・・・TestClassBase(パスカルケース)
    Blade内・・・x-test-class-base(ケバブケース)

    コンポーネントクラス内で
    public funtion render(){
        return view('bladeコンポーネント名')
    }
</div>
```

5\. コンテンツを渡す側のファイルを編集する
```php:resources/views/tests/component-test2.blade.php
<x-tests.app>
    <x-slot name="header">ヘッダー２</x-slot>
    コンポーネントテスト２
    <x-test-class-base /> // 追加
</x-tests.app>
```

6\. ローカルサーバを立ち上げて確認する
```
php artisan serve
```
- http://127.0.0.1:8000/component-test2
- 表示デザインが変更されている。
- コンテンツを渡す側のファイルにクラス名を指定してあげると(1)クラスが呼び出される(TestClassBase.php)。(2)renderメソッドの中に記載してあるコンポーネントが表示される(test-class-base.blade.php)。

## sec09 Componentのパターン - クラスベースと匿名コンポーネントの違い
- クラスベースはclassなのでコンストラクタを使用できる。
- Laravel Breezeで追加されるファイルは匿名コンポーネントで作られるのでまずはこちらの方法を覚える。
- 変数などを分離させたい場合はクラスベースで作る。

1\. Bladeコンポーネント側でコンテンツを表示させたい箇所に変数を設定する
```php:resources/views/components/tests/test-class-base.blade.php
<div>
    クラスベースのコンポーネントです。
    使用する場合は
    App/View/Components内のクラスを指定する。
    クラス名・・・TestClassBase(パスカルケース)
    Blade内・・・x-test-class-base(ケバブケース)

    コンポーネントクラス内で
    public funtion render(){
        return view('bladeコンポーネント名')
    }
    <div>{{ $classBaseMessage }}</div>
</div>
```

2\. コンテンツを渡す側のファイルを編集する
```php:resources/views/tests/component-test2.blade.php
<x-tests.app>
    <x-slot name="header">ヘッダー２</x-slot>
    コンポーネントテスト２
    <x-test-class-base classBaseMessage="メッセージです" /> // 追加:test-class-baseがクラスベースのコンポーネントなのでこの後に属性を付ける。
</x-tests.app>
```
- 次に、クラスベースのclassに使用する属性を作成する必要がある。

3\. クラスのファイルを編集する
```php:app/View/Components/TestClassBase.php
class TestClassBase extends Component
{
    public $classBaseMessage;
    /**
     * Create a new component instance.
     */
    public function __construct($classBaseMessage)
    {
        $this->classBaseMessage = $classBaseMessage;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string // 補足：コンストラクタで設定している場合はrenderメソッドの方で変数を渡す必要はない。特に変更なし
    {
        return view('components.tests.test-class-base');
    }
}

```

4\. ローカルサーバを立ち上げて確認する
```
php artisan serve
```
- http://127.0.0.1:8000/component-test2

---

5\. クラスベース - 初期値の設定 
 クラスのファイルを編集する
```php:app/View/Components/TestClassBase.php
class TestClassBase extends Component
{
    public $classBaseMessage;
    public $defaultMessage; // 追加
    /**
     * Create a new component instance.
     */
    public function __construct($classBaseMessage, $defaultMessage="初期値です。") // 追加
    {
        $this->classBaseMessage = $classBaseMessage;
        $this->defaultMessage = $defaultMessage; // 追加
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.tests.test-class-base');
    }
}

```

6\. Bladeコンポーネント側でコンテンツを表示させたい箇所に初期値を設定する
```php:resources/views/components/tests/test-class-base.blade.php
<div>
・・・
    <div>{{ $classBaseMessage }}</div>
    <div>{{ $defaultMessage }}</div>
</div>
```

7\. コンテンツを渡す側のファイルを編集する
```php:resources/views/tests/component-test2.blade.php
<x-tests.app>
    <x-slot name="header">ヘッダー２</x-slot>
    コンポーネントテスト２
    <x-test-class-base classBaseMessage="メッセージです" />
    <div class="mb-4"></div> // 追加：ただ見やすいように改行
    <x-test-class-base classBaseMessage="メッセージです" defaultMessage="初期値から変更しています" /> // 追加
</x-tests.app>
```

8\. ローカルサーバを立ち上げて確認する
```
php artisan serve
```
- http://127.0.0.1:8000/component-test2
- コンポーネントを2回使っているのでクラスが2回表示される。
- 初期値の箇所を設定していないとコンストラクター側で設定した値が入り、上書きすると値が変更されて表示される

## sec10 サービスコンテナ 作成
1\. 準備としてルーティングファイルに追記する
```php:routes/web.php
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComponentTestController;
use App\Http\Controllers\LifeCycleTestController; // 追記
・・・
Route::get('/component-test1', [ComponentTestController::class, 'showComponent1']);
Route::get('/component-test2', [ComponentTestController::class, 'showComponent2']);
Route::get('/servicecontainertest', [LifeCycleTestController::class, 'showServiceContainerTest']); // 追記
```

2\. コマンドでコントローラを作成
```
php artisan make:controller LifeCycleTestController
```

3\.  コントローラの中でメソッドが必要なので作成されたコントローラファイルを編集する
```php:app/Http/Controllers/LifeCycleTestController.php
class LifeCycleTestController extends Controller
{
    // 追記
    public function showServiceContainerTest()
    {
        dd(app()); // appのヘルパー関数を使うとサービスコンテナの中身を確認できる
    }
}
```

4\. この状態でローカルサーバを立ち上げて確認する
```
php artisan serve
```
- http://127.0.0.1:8000/servicecontainertest
- bindings: サービスコンテナに登録されているサービスの数(71と表示されている)

5\. サービスコンテナに登録する（コントローラファイル）
- app()->bind('lifeCycleTest', function(){
    return 'ライフサイクルテスト';
});
引数(取り出す時の名前, 機能);
Bindinsgs:の数が71->72に増える

```php:app/Http/Controllers/LifeCycleTestController.php
class LifeCycleTestController extends Controller
{
    //
    public function showServiceContainerTest()
    {
        app()->bind('lifeCycleTest', function(){
            return 'ライフサイクルテスト';
        }); // 追記
        
        dd(app());
    }
}
```
- 再度ローカルサーバを立ち上げて確認する(http://127.0.0.1:8000/servicecontainertest)
- lifeCycleTestという項目が追加されている

6\. サービスコンテナから取り出す
- $test = app()->make('lifeCycleTest');

- 他の書き方
$test = app('lifeCycleTest');
$test = resolve('lifeCycleTest');
$test = App::make('lifeCycleTest');

```php:app/Http/Controllers/LifeCycleTestController.php
class LifeCycleTestController extends Controller
{
    //
    public function showServiceContainerTest()
    {
        app()->bind('lifeCycleTest', function(){
            return 'ライフサイクルテスト';
        });

        $test = app()->make('lifeCycleTest'); // 追加

        dd($test, app()); // 編集
    }
}
```

## sec10 サービスコンテナ 依存関係
0\. 依存関係の解決
- 依存した2つのクラス
- それぞれインスタンス化後に実行
```php
$message = new Message();
$sample = new Sample($message);
$sample->run();
```

- サービスコンテナを使ったパターン
```php
app->bind('sample', Sample::class);
$sample = app()->make('sample');
$sample->run();
```
- サービスコンテナを使っておくとbindで紐づけてapp()->makeで使う際に関連するクラス(依存してるクラス)も同時にインスタンス化してくれる
- new でインスタンス化しなくても使用できる

1\. コントローラファイルを編集する
- 本来は1ファイルに1クラスだがテストのため複数のクラスを書いていく
```php:app/Http/Controllers/LifeCycleTestController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LifeCycleTestController extends Controller
{
    //
    public function showServiceContainerTest()
    {
        app()->bind('lifeCycleTest', function(){
            return 'ライフサイクルテスト';
        });

        $test = app()->make('lifeCycleTest');

        // ③追加
        // サービスコンテナなしのパターン
        $message = new Message(); // インスタンス化
        $sample = new Sample($message); //クラスのインスタンスを引数に渡している
        $sample->run();

        dd($test, app());
    }
}

// ②追加：Sampleクラスの方でconstructで初期化するときにMessageクラスも使うと設定する。
// Sampleクラスを使うときは予めMessageクラスをインスタンス化しておく必要がある。
class Sample
{
    public $message;
    // インスタンス化する時にメッセージも読み込む
    // DI：引数（Message）の方にクラス名を入れてあげると自動的にインスタンス化してくれる
    public function __construct(Message $message){
        $this->message = $message;
    }
    public function run(){
        $this->message->send();
    }
}

// ①追加
class Message 
{
    public function send(){
        echo('メッセージ表示');
    }
}

```

2\. ローカルサーバを立ち上げて確認する
```
php artisan serve
```
- http://127.0.0.1:8000/servicecontainertest
- 一番上に「メッセージ表示」とされていることがわかる

- サービスコンテナを使わないパターンであればそれぞれのクラスを一度インスタンス化してあげれば使用可能

3\. サービスコンテナありのパターンでコントローラファイルを編集する
```php:app/Http/Controllers/LifeCycleTestController.php
<?php
// サービスコンテナなしのパターン
// $message = new Message();
// $sample = new Sample($message);
// $sample->run();

// サービスコンテナ「app()」ありのパターン
app()->bind('sample', Sample::class); // app()のヘルパー関数。bindで紐づける。紐づける際に呼び出す名前を付ける必要があるのでSampleとしておく。今回classを付けるということでSample::classと書いてあげるとclassを紐づけることができる。
$sample = app()->make('sample'); // サービスコンテナから取り出す処理はapp()->make 呼び出す名前はsampleとすればOK。これを変数に置く必要があるので$sampleとしておく。
// sampleの中のrunメソッドを表示する。
$sample->run();

dd($test, app());
```

4\. 再度ローカルサーバを立ち上げて確認する
```
php artisan serve
```
- http://127.0.0.1:8000/servicecontainertest
- サービスコンテナなしと同様一番上に「メッセージ表示」とされていることがわかる
- 見た目は同じだが、サービスコンテナを使用する場合newのインスタンス化をしなくても使用できる。
- Sample::classでMessage内のクラスも設定する必要があったが、自動的に依存関係を解決してこの「app()->make('sample')」だけで使用できるようになっていることが特徴。

## sec11 サービスプロバイダー 使用方法
1\. ルーティングファイルを編集する
```php:routes/web.php
Route::get('/component-test1', [ComponentTestController::class, 'showComponent1']);
Route::get('/component-test2', [ComponentTestController::class, 'showComponent2']);
Route::get('/servicecontainertest', [LifeCycleTestController::class, 'showServiceContainerTest']);
Route::get('/serviceprovidertest', [LifeCycleTestController::class, 'showServiceProviderTest']); // 追加（URL, メソッド）
```
2\. コントローラファイルを編集する
```php:app/Http/Controllers/LifeCycleTestController.php
class LifeCycleTestController extends Controller
{
    // 追加
    public function showServiceProviderTest()
    {
        $encrypt = app()->make('encrypter');
        $password = $encrypt->encrypt('password');
        dd($password, $encrypt->decrypt($password));
    }
    
    public function showServiceContainerTest()
```

1\. ローカルサーバを立ち上げて確認する
```
php artisan serve
```
- http://127.0.0.1:8000/serviceprovidertest
- 画面にはencryptメソッドを使って「password」が暗号化されて文字とdecryptメソッドを使って元に戻した文字「password」が表示されている

## sec11 サービスプロバイダー 生成
1\. サービスプロバイダーを生成する
```
php artisan make:provider SampleServiceProvider
```
- App/Providers配下に生成

- 補足
```php
public function register(){
    サービスを登録するコード
}

public function boot(){
    前サービスプロバイダー読み込み後に
    実行したいコード
}
```

2\. コントローラファイルを編集する
```php:app/Http/Controllers/LifeCycleTestController.php

```

- 復習
```php:app/Http/Controllers/LifeCycleTestController.php
<?php
// app()->bindでサービスコンテナに登録した名前とその処理の内容を書いている
public function showServiceContainerTest()
{
    app()->bind('lifeCycleTest', function(){
        return 'ライフサイクルテスト';
        });
}
```

2\. 実際にプロバイダファイルにも記述してみる
```php:app/Providers/SampleServiceProvider.php
public function register(): void
    {
        app()->bind('ServiceProviderTest', function(){
            return 'サービスプロバイダのテスト';
        });
    }
```
- 次、このSampleServiceProviderを実際に起動するときに読み込むためのconfigフォルダの中のapp.phpを追記する

3\. configファイルを編集する
```php:config/app.php
'providers' => ServiceProvider::defaultProviders()->merge([
    /*
        * Package Service Providers...
        */

    /*
        * Application Service Providers...
        */
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    // App\Providers\BroadcastServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    App\Providers\SampleServiceProvider::class // 追加
])->toArray(),
```
- これでSampleServiceProviderもサービスコンテナに登録されて使えるようになった。

4\. 登録したものを実際に表示させてみる
```php:app/Http/Controllers/LifeCycleTestController.php
public function showServiceProviderTest()
{
    $encrypt = app()->make('encrypter');
    $password = $encrypt->encrypt('password');

    $sample = app()->make('serviceProviderTest'); // 追加

    dd($sample, $password, $encrypt->decrypt($password)); // $sampleをddの中へ入れて表示させる
}
    
```

1\. ローカルサーバを立ち上げて確認する
```
php artisan serve
```
- http://127.0.0.1:8000/serviceprovidertest
- 一番上に「サービスプロバイダのテスト」という文字が表示されている
- サービスプロバイダのファイルを作成してPHPの中に書いてあげると自動的にサービスコンテナの中に登録される。それをコントローラの中などでいつでも使用できる。
