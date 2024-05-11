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