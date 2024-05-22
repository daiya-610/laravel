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


1\.
```
```

1\.
```
```

1\.
```
```

1\.
```
```

1\.
```
```

1\.
```
```

1\.
```
```

1\.
```
```

1\.
```
```

1\.
```
```

1\.
```
```

1\.
```
```

1\.
```
```

1\.
```
```

1\.
```
```
