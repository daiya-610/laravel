## sec100 マルチログイン
1\. モデルとマイグレーションの生成
```
php artisan make:model Owner -m
php artisan make:model Admin -m
```
- -mでマイグレーションファイルも生成される
- app/models フォルダ以下に生成される
Authenticatable を継承

2\. 認証機能をつけるためにModelsファイルを編集する
```php:app/Models/Owner.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable; // 追加

class Owner extends Authenticatable // 編集：ModelからAuthenticatableに変更。認証機能を付ける。
{
    use HasFactory;

    // 追加
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
    */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}

```
- app/Models/Admin.php も同様な設定をする

```php:app/Models/Admin.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}

```

3\. マイグレーションファイルを編集する
```php:database/migrations/2024_05_09_224844_create_owners_table.php
public function up(): void
    {
        Schema::create('owners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }
```
- $tableのidからtimestampsまでAdminも同様の設定をする。

4\. マイグレーションの実行
- 実行前にMAMPなどでデータベースを立ち上げておく。
```
php artisan migrate
```

5\. マイグレーション設定
- マイグレーションで「パスワードリセット」という機能があり、テーブルもある。
- Owner, Adminそれぞれテーブル作成する。
```
php artisan make:migration create_owner_password_resets
php artisan make:migration create_admin_password_resets
```

6\. create_password_reset_tokens_tableの内容を参考に作成されたowner, adminにそれぞれ編集する
```php:database/migrations/2024_05_09_232134_create_owner_password_resets.php
public function up(): void
    {
        Schema::create('owner_password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }
```
- $tableのemailからtimestampまでをAdminにも反映させる

7\. 改めてマイグレーションを実行する
```
php artisan migrate
```
- もしマイグレーションをやり直したい場合は下記コマンドを実行する
```
php artisan migrate:refresh
```
- 登録しているデータが消えてしまうが全てのマイグレーションをやり直すことができる

8\. MAMPなどでデータベースの中身を確認する
- 生成されたマイグレーションのテーブルがあることを確認する

## sec100 マルチログイン - ルート設定
- Userで使っているのは web.php と auth.php(これはLaravelを生成した時点であるファイル)
- 下記をそれぞれ作成する
- Owner用の routes/owner.php
- Admin用の routes/admin.php

1\. auth.phpはユーザーのルート情報になるのでこれを参考にownerとadminのルート情報を作成する
- まずはファイル作成
```
touch routes/owner.php
touch routes/admin.php
```

2\. web.phpをコピーし、owner.phpに貼り付ける
```php:routes/owner.php
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComponentTestController;
use App\Http\Controllers\LifeCycleTestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/component-test1', [ComponentTestController::class, 'showComponent1']);
Route::get('/component-test2', [ComponentTestController::class, 'showComponent2']);
Route::get('/servicecontainertest', [LifeCycleTestController::class, 'showServiceContainerTest']);
Route::get('/serviceprovidertest', [LifeCycleTestController::class, 'showServiceProviderTest']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
```

3\. 不要なコードを削除する
```php:routes/owner.php
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
 // 削除：テストコントローラたちは不要

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// 削除：テストコードたちは不要

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

```

## sec106 ビュー（レイアウト）
1\. Layoutフォルダ内も編集が必要
- navigation.blade.phpをそれぞれ
- user-navigation.blade.php
- owner-navigation.blade.php
- admin-navigation.blade.php とコピーする

2\. App.blade.phpに条件追加（auth(ガード)）
```
@if(auth('admin')->user())
    @include('layouts.admin-navigation')
@elseif(auth('owners')->user())
```

---
---

## sec110 ロゴ設定
- 画像の保存場所・・・基本的に2種類ある
1) publicフォルダに直接置く・・・初期ファイル ←今回はこっち
2) storageフォルダ・・・フォルダ内画像はgitHubにアップロードしないという設定になっている
- 表側(public)から見れるようにリンク
- php artisan storage:link
- public/storage リングが生成される

- asset() ヘルパ関数でpublic内のファイルを指定

- assets("images/logo.jpg")を
components/application-logo.blade.phpに記載

- 表示された画像が大きい、調整する場合はviewsのファイルで調整する
1\. 画像の大きさ調整
```php:resources/views/admin/auth/login.blade.php

```

## sec111 リソースコントローラー
1\.下記コマンドを叩く
```
php artisan make:controller Admin/OwnersController --resource
```

2\. Routeのファイルを編集する
```php:routes/admin.php
use App\Http\Controllers\Admin\OwnersController;

Route::resource('owners', OwnersController::class); // この状態だとログインしていなくても表示されてしまうためログインしてるかどうかの認証を追加をする
->middleware('auth:admin');
```

3\. コントローラ側でも設定を追加する
```php:app/Http/Controllers/Admin/OwnersController.php
public function __construct()
    {
        $this->middleware('auth:admin');
    }
```

## sec112 シーダー（ダミーデータ）作成
1\.database/seeders 直下に生成
```
php artisan make:seeder AdminSeeder
php artisan make:seeder OwnerSeeder
```

2\. シーダー(ダミーデータ)の設定方法
- 2種類ある - 手動か自動化
- DBファサードのinsertで連想配列で追加
- パスワードがあればHashファサードも使う
```php:手動
DB:tables('owners')->insert([
    ['name' => 'test'], 'email' => 'test1@test.com',
    Hash::make('password123')]
]);
```
```php
// DatabaseSeeder.php内で読み込み設定
$this->call([
    AdminSeeder, OwnerSeeder
]);
```

3\. シーダーファイルの編集
```
public function run(): void
    {
        DB::table('admins')->insert([
            'name' => 'test',
            'email' => 'test@test.com',
            'password' => Hash::make('password123'),
            'created_at' => '2024/05/05 11:11:11'
        ]);
    }
```

4\. シーダーの実行
```
php artisan db:seed
```
- デフォルト：Database/Seeders/DatabaseSeederクラスを実行

```
php artisan db:seed --class=UserSeeder
```
- --classオプションを使用して個別に実行する特定のシーダークラスを指定できる

```
php artisan migrate:fresh --seed
// 前テーブルを削除してup()を実行
```

```
php artisan migrate:refresh --seed
// down()を実行後up()を実行
```

- migrate:fresh コマンドを --seed オプションと組み合わせて使用してデータベースをシードすることもできる。
- これにより全てのテーブルが削除され、全てのマイグレーションが再実行される。
- このコマンドはデータベースを完全に再構築するのに役立つ

4-1\. シーダーを実行すると下記エラーが出る
```
$ php artisan db:seed
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'test@test.com' for key 'admins_email_unique' (Connection: mysql, SQL: insert into `admins` (`name`, `email`, `password`, `created_at`) values (test, test@test.com, $2y$12$78quGcNcr0cAGSsksealUOF3oVbJ3BcKixAyiVb5uRKGIGisi87Lq, 2024/05/05 11:11:11))
```
- メールアドレスが重複しているので登録できないというエラーが出ている。
- テーブル毎に作る必要がある。

```
php artisan migrate:refresh --seed
```

## sec113 データを扱う方法　比較
|  | コレクション <br> (Collection) | クエリビルダ <br> (QueryBuilder) | エロクアント <br> (Eloquent(モデル)) |
| ---- | ---- | ---- | ---- |
| データ型 | Illuminate\Support\Collection | Illuminate\Support\Collection | Illuminate\Database\Eloquent\Collection(Collection を継承) |
| 使用方法 | collect(); <br> new Collection; | use Illuminate\Support\Facades\DB; <br> DB:table(テーブル名)->get(); | モデル名::all(); <br> モデル名::select()->get(); |
| 関連マニュアル | コレクション | コレクション <br> クエリビルダ | コレクション、クエリビルダ、エロクアント、エロクアントのコレクション |
| 特徴 | 配列を拡張 | SQLに近い | ORマッパー |
| メリット | 多数の専用メソッド | SQLを知っているとわかやすい | 簡潔にかける <br> リレーションが強力 |
| デメリット | 返り値に複数のパターンあり <br> (stdClass, Collection, モデルCollection) | コードが長くなりがち | 覚えることが多い <br> やや遅い |

1\. コントローラファイルを編集する
```php:app/Http/Controllers/Admin/OwnersController.php
use App\Models\Owner; // Eloquent エロクアント
use Illuminate\Support\Facades\DB; // QueryBuilder クエリビルダ

public function index()
    {
        $e_all = Owner::all();
        $q_get = DB::table('owners')->select('name')->get();
        $q_first = DB::table('owners')->select('name')->first();

        $c_test = collect([
            'name' => 'てすと'
        ]);

        dd($e_all, $q_get, $q_first, $c_test);
    }
```
- queryBuilderのfirstに関してはver_dumpを使うとスタンダードクラスだとわかりやすい
1-1\.
```php:app/Http/Controllers/Admin/OwnersController.php
var_dump($q_first);
```
- object(stdClass)#1495 (1) { ["name"]=> string(5) "test1" }
- Laravelでデータを扱う際はコレクションを扱うことが多い。
- コレクションの中にもSupportのコレクション or エロクアントのコレクション 2種類ある。
- Collection or stdClassで返ってくるパターンもある。
- データがうまく返ってこない場合は「dd」などを使ってデータの型を見つつ進めていく必要がある。

## sec114 Carbon ライブラリ
- Carbon
PHPのDateTimeクラスを拡張した
日付ライブラリ
Laravelに標準搭載
- 公式サイト： https://carbon.nesbot.com/
- 個人ブログ： https://coinbaby8.com/carbon-laravel.html
- エロクアントのtimestampはCarbonインスタンス
- $eloquents->created_at->diffForHumans()
- クエリビルダでCarbonを使うなら
- Carbon\Carbon::parse($query->created_at)->diffForHumans()

1\. コントローラファイルを編集する
```php:app/Http/Controllers/Admin/OwnersController.php
use Carbon\Carbon;

echo $date_now;
echo $date_parse;
```

2\. Carbonをエロクアントとクエリビルダで表示する方法
```php:app/Http/Controllers/Admin/OwnersController.php
$e_all = Owner::all();
$q_get = DB::table('owners')->select('name', 'created_at')->get();

return view('admin.owners.index', compact('e_all', 'q_get'));
```

3\. viewsのフォルダ・ファイルを1つ作成する
```
mkdir resources/views/admin/owners
touch resources/views/admin/owners/index.blade.php
```

- admin直下のdashboard.blade.phpをコピーし貼り付け・編集する
```php:resources/views/admin/owners/index.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    エロクアント
                    @foreach ($e_all as $e_owner)
                      {{ $e_owner->name }}
                      {{ $e_owner->created_at->diffForHumans() }}
                    @endforeach
                    <br>
                    クエリビルダ
                    @foreach ($q_get as $q_owner)
                      {{ $e_owner->name }}
                      {{ Carbon\Carbon::parse($e_owner->created_at)->diffForHumans() }}
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

## sec115 admin/owners　一覧画面（tailblocks使用）
1\. コントローラファイルを編集する
```php:resources/views/admin/owners/index.blade.php
public function index()
    {
        // $date_now = Carbon::now();
        // $date_parse = Carbon::parse(now());
        // echo $date_now->year;
        // echo $date_parse;

        // $e_all = Owner::all();
        // $q_get = DB::table('owners')->select('name', 'created_at')->get();
        // $q_first = DB::table('owners')->select('name')->first();

        // $c_test = collect([
        //     'name' => 'てすと'
        // ]);

        // var_dump($q_first);

        // dd($e_all, $q_get, $q_first, $c_test);
        $owners = Owner::select('name', 'email', 'created_at')->get();

        return view('admin.owners.index',
        compact('owners'));
    }
```

2\. view側の内容も編集する
```php:resources/views/admin/owners/index.blade.php
<div class="p-6 text-gray-900">
    @foreach ($owners as $owner)
        {{ $owner->name }}
        {{ $owner->email }}
        {{ $owner->created_at->diffForHumans() }}
    @endforeach
    {{-- エロクアント
    @foreach ($e_all as $e_owner)
        {{ $e_owner->name }}
        {{ $e_owner->created_at->diffForHumans() }}
    @endforeach
    <br>
    クエリビルダ
    @foreach ($q_get as $q_owner)
        {{ $e_owner->name }}
        {{ Carbon\Carbon::parse($e_owner->created_at)->diffForHumans() }}
    @endforeach --}}
</div>
```

3\.tailblocksのコードをコピーして持ってくる
- 公式サイト： https://tailblocks.cc/
- PRICINGの二番目、色：デフォルト
```php:resources/views/admin/owners/index.blade.php
<section class="text-gray-600 body-font">
  <div class="container px-5 py-24 mx-auto">
    <div class="flex flex-col text-center w-full mb-20">
      <h1 class="sm:text-4xl text-3xl font-medium title-font mb-2 text-gray-900">Pricing</h1>
      <p class="lg:w-2/3 mx-auto leading-relaxed text-base">Banh mi cornhole echo park skateboard authentic crucifix neutra tilde lyft biodiesel artisan direct trade mumblecore 3 wolf moon twee</p>
    </div>
    <div class="lg:w-2/3 w-full mx-auto overflow-auto">
      <table class="table-auto w-full text-left whitespace-no-wrap">
        <thead>
          <tr>
            <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">Plan</th>
            <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">Speed</th>
            <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">Storage</th>
            <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">Price</th>
            <th class="w-10 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tr rounded-br"></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="px-4 py-3">Start</td>
            <td class="px-4 py-3">5 Mb/s</td>
            <td class="px-4 py-3">15 GB</td>
            <td class="px-4 py-3 text-lg text-gray-900">Free</td>
            <td class="w-10 text-center">
              <input name="plan" type="radio">
            </td>
          </tr>
          <tr>
            <td class="border-t-2 border-gray-200 px-4 py-3">Pro</td>
            <td class="border-t-2 border-gray-200 px-4 py-3">25 Mb/s</td>
            <td class="border-t-2 border-gray-200 px-4 py-3">25 GB</td>
            <td class="border-t-2 border-gray-200 px-4 py-3 text-lg text-gray-900">$24</td>
            <td class="border-t-2 border-gray-200 w-10 text-center">
              <input name="plan" type="radio">
            </td>
          </tr>
          <tr>
            <td class="border-t-2 border-gray-200 px-4 py-3">Business</td>
            <td class="border-t-2 border-gray-200 px-4 py-3">36 Mb/s</td>
            <td class="border-t-2 border-gray-200 px-4 py-3">40 GB</td>
            <td class="border-t-2 border-gray-200 px-4 py-3 text-lg text-gray-900">$50</td>
            <td class="border-t-2 border-gray-200 w-10 text-center">
              <input name="plan" type="radio">
            </td>
          </tr>
          <tr>
            <td class="border-t-2 border-b-2 border-gray-200 px-4 py-3">Exclusive</td>
            <td class="border-t-2 border-b-2 border-gray-200 px-4 py-3">48 Mb/s</td>
            <td class="border-t-2 border-b-2 border-gray-200 px-4 py-3">120 GB</td>
            <td class="border-t-2 border-b-2 border-gray-200 px-4 py-3 text-lg text-gray-900">$72</td>
            <td class="border-t-2 border-b-2 border-gray-200 w-10 text-center">
              <input name="plan" type="radio">
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="flex pl-4 mt-4 lg:w-2/3 w-full mx-auto">
      <a class="text-indigo-500 inline-flex items-center md:mb-2 lg:mb-0">Learn More
        <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="w-4 h-4 ml-2" viewBox="0 0 24 24">
          <path d="M5 12h14M12 5l7 7-7 7"></path>
        </svg>
      </a>
      <button class="flex ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">Button</button>
    </div>
  </div>
</section>
```

2\.
```php:resources/views/admin/owners/index.blade.php
 <tbody>
@foreach ($owners as $owner)<tr>
    <td class="px-4 py-3">{{ $owner->name }}</td>
    <td class="px-4 py-3">{{ $owner->email }}</td>
    <td class="px-4 py-3">{{ $owner->created_at->diffForHumans() }}</td>
    <td class="w-10 text-center">
    <input name="plan" type="radio">
    </td>
</tr>
@endforeach
</tbody>
```

## sec116 admin/owners 一覧画面（tailblock Contact Us使用）

1\. viewsのadminにファイルを作成する
```
touch resources/views/admin/owners/create.blade.php
```

2\. dashboard.blade.phpの内容をコピーしつつ、tailblocksからコードを持ってくる。
```
cp resources/views/admin/dashboard.blade.php resources/views/admin/owners/create.blade.php
```

```php:resources/views/admin/owners/create.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }} // ←ここを削除してtailblocksの Contact Usのコードを全て追加する
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

4\. この状態で表示させたいがコントローラ側でviewヘルパ関数を書いてあげないといけないので編集する
```php:app/Http/Controllers/Admin/OwnersController.php
public function create()
{
  return view('admin.owners.create');
}
```

5\. ローカルサーバで確認する
```
php artisan serve
```
- http://127.0.0.1:8000/admin/owners/create

6\. admin/owners/create画面のデザインを整えていく
```php:resources/views/admin/owners/create.blade.php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            オーナー登録
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <section class="text-gray-600 body-font relative">
                        <div class="container px-5 mx-auto">
                          <div class="flex flex-col text-center w-full mb-12">
                            <h1 class="sm:text-3xl text-2xl font-medium title-font mb-4 text-gray-900">オーナー登録</h1>
                          </div>
                          <div class="lg:w-1/2 md:w-2/3 mx-auto">
                            <div class="-m-2">
                              <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                  <label for="name" class="leading-7 text-sm text-gray-600">Name</label>
                                  <input type="text" id="name" name="name" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                </div>
                              </div>
                              <div class="p-2 w-1/2 mx-auto">
                                <div class="relative">
                                  <label for="email" class="leading-7 text-sm text-gray-600">Email</label>
                                  <input type="email" id="email" name="email" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                </div>
                              </div>
                              <div class="p-2 w-full flex justify-around mt-4">
                                <button onclick="location.href='{{ route('admin.owners.index')}}'" class="bg-gray-200 border-0 py-2 px-8 focus:outline-none hover:bg-igray-400 rounded text-lg">戻る</button>
                                <button class="bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">登録する</button>
                              </div>
                          </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

7\. admin/ownersにもボタンを追加する
```php:resources/views/admin/owners/index.blade.php
<div class="flex justify-end mb-4">
    <button onclick="location.href='{{ route('admin.owners.create')}}'" class="bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">新規登録する</button>
</div>
```

8\. オーナー登録においてパスワードの項目も必要だったので
パスワードのインプットタグを用意する
```php:resources/views/admin/owners/create.blade.php
<div class="-m-2">
    <div class="p-2 w-1/2 mx-auto">
    <div class="relative">
        <label for="name" class="leading-7 text-sm text-gray-600">オーナー名</label>
        <input type="text" id="name" name="name" required class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
    </div>
    </div>
    <div class="p-2 w-1/2 mx-auto">
    <div class="relative">
        <label for="email" class="leading-7 text-sm text-gray-600">メールアドレス</label>
        <input type="email" id="email" name="email" required class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
    </div>
    </div>
    <div class="p-2 w-1/2 mx-auto">
    <div class="relative">
        <label for="password" class="leading-7 text-sm text-gray-600">パスワード</label>
        <input type="password" id="password" name="password" required class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
    </div>
    </div>
    <div class="p-2 w-1/2 mx-auto">
    <div class="relative">
        <label for="password_confirmation" class="leading-7 text-sm text-gray-600">パスワード確認</label>
        <input type="password_confirmation" id="password_confirmation" name="password_confirmation" required class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
    </div>
    </div>
    <div class="p-2 w-full flex justify-around mt-4">
    <button onclick="location.href='{{ route('admin.owners.index')}}'" class="bg-gray-200 border-0 py-2 px-8 focus:outline-none hover:bg-igray-400 rounded text-lg">戻る</button>
    <button class="bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">登録する</button>
    </div>
</div>
```

## sec117 登録する処理 - CRUD(Store)
- 前回まではcreate画面でフォームを用意した。
- 今回は登録するボタンで登録処理を行う。
- Formタグ、method="post" action=store 指定
- @csrf 必須
- 戻るボタンは type="button"をつけておく
- inputタグ name="" 属性を
Request $request インスタンスで取得
dd($request->name);
- 保存する際にバリデーション機能を追加。
- 1. View
バリデーションで画面読み込み後も入力した値を保持したい場合
- <input name="email" value="{{ old('email') }}">
- 2. Model - プロパティで必要な情報を指定する必要がある。
$fillable or $guarded で設定
- procted $fillable = [
    'name',
    'email',
    'password',
];
- 3. Controller - バリデーション設定
簡易バリデーション or カスタムリクエスト
- $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|string|email|max:255|unique:owners',
    'password' => 'required|string|confirmed|min:8',
]);
- 4. Controller - 保存処理
Owner::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
]);
return redirect()->route('admin.owners.index'); 


## sec118 Store フラッシュメッセージ1
- 英語だとtoaster
- Sessionを使って1度だけ表示

- Controller側
```
session()->flash('message', '登録できました。');
Session::flash('message', '');
# 数秒後に消したい場合はJSも必要
```

- View側(コンポーネント)
```
@props(['status' => 'info'])

@php
if($status === 'info') { $bgColor = 'bg-blue-300'; }
if($status === 'error') { $bgColor = 'bg-red-500'; }
@endphp

@if(session('message'))
    <div class="{{ $bgColor }} w-1/2 mx-auto p-2 text-white">
        {{ session('message' )}}
    </div>
@endif

```

- View側 <x-flash-messae status="info" />

sec119_resourceRestfulControllerEditUpdate
1\. Edit 編集
- ルート情報確認コマンド
```
php artisan route:list | grep admin.
```

- Controller側
```
$owner = Owner::findOrFail($id); // idなければ404画面
```

- View側/edit
```
{{ $owner->name}}

- View側/index 名前付きルート 第2引数にidを指定
route('admin.owners.edit', ['owner' => $owner->id ]);
```

## sec120 resourceController Update（更新）
- Controller側
```
$owner = Owner::findOrFail($id);
$owner->name = $request->name;
$owner->email = $request->email;
$owner->password = Hash::make($request->password);
$owner->save();

redirect()->route()->with();
```

## sec121 Delete ソフトデリート
1）論理削除（ソフトデリート）->復元できる（（ゴミ箱）
2）物理削除（デリート）->復元できない

- マイグレーション側
```
$table->softDeletes();
```

- モデル側
```
use Illuminate\Database\Eloquent\SoftDeletes;
```

- モデルのクラス内
```
use SoftDeletes;
```

- 追加後はmigrationコマンドを叩く
```
php artisan migrate:refresh --seed
```

- コントローラ側
```
Owner::findOrFail($id)->delete(); // ソフトデリート
Owner::all(); // ソフトデリートしたものは表示されない
Owner::onlyTrashed()->get(); // ゴミ箱のみ表示
Owner::withTrashed()->get(); // ゴミ箱も含め表示

Owner::onlyTstashed()->restore(); // 復元
Owner::onlyTrashed()->forceDelete(); // 完全削除

$owner->trashed() // ソフトデリートされているかの確認
```

- Delete アラート表示（JS）
```
<form id="delete_{{$owner->id}}" method="post"
action = "{{ route('admin.owners.destroy', ['owner' => $owner->id])}}">
    @csrf @method('delete')
<a href="#" data-id="{{ $owner->id }}" onclick="deletePost(this)">削除</a>

<script>
    function deletePost(e) {
        'use strict';
        if(confirm('本当に削除してもいいですか？')) {
            document.getElementById('delete_' + e.dataset.id).submit();
        }
    }
</script>
```

## sec123 ソフトデリート（期限切れオーナー）
- 月額会員・年間会員で更新期限切れ
->延滞料金を払ったら戻せる、など。
->復旧できる手段を残しておく。

- View: admin/expired-owners.blade.php

- 注意：データとして残るので同じメールアドレスで新規登録できない
->復旧方法などの案内が別途必要。

- 期限切れオーナー - Route側
```
Route::prefix('expired-owners')->
middleware('auth:admin')->group(
    function(){
        Route::get('index', [OwnersController::class, 'expiredOwnerIndex'])->name('expired-owners.index');
        Route::post('destroy/{owner}', [OwnersController::class, 'expiredOwnerDestroy'])->name('expired-owners.destroy');
    }
);
```

- 期限切れオーナー - Controller側
```
public function expiredOwnerIndex() {
    $expiredOwners = Owner::onlyTrashed()->get(); // onlyTrashed()でソフトデリートしたデータだけ取得できる（ソフトデリートした日付けなど）
    return view('admin.expired-owners',
    compact('expiredOwners'));
}

public function expiredOwnerDestroy($id) {
    Owner::onlyTrashed()->findOrFail($id)->forceDelete();
    return redirect()->route('admin.expired-owners.index');
}
```

- 期限切れオーナー - View側
```
@foreach ($expiredOwners as $owner)

<form id="delete_{{$owner->id}}" method="post" action="{{ route('admin.expired-owners.destroy', ['owner' => $owner->id])}}">
@csrf
<td class="px-4 py-3 text-center">
<a href="#" data-id="{{ $owner->id}}" onclick="deletePost(this)" class="text-white bg-red-400 border-0 p-2 focus:outline-none hover:bg-red-500 rounded">完全に削除</a>
</td>
</form>
```

## sec124_ページネーション
- オーナーの数が増えてくるとリストが長くなるので
paginationを設定しておく。

- Controller側
```
$owners = Owner::select('id', 'name', 'email', 'created_at', 'desc')
    ->orderBy('created_at', 'desc')
    ->paginate(3);
```

- View側
```
{{ $owners->links() }}
```

1\. ページネーションの日本語か
- vendorフォルダ内ファイルをコピー
```
php artisan vendor:publish --tag=laravel-pagination
```

2\. resources/views/vendor/pagination/tailwindcss.blade.php
- ファイル内を編集

1\.
```
```


1\.
```
```


1\.
```
```
