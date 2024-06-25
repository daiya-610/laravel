# オーナー概要
## sec200 オーナーでできること
- オーナープロフィール編集
- 店舗情報更新(1オーナー 1店舗)
- 画像登録
- 商品登録
（画像、カテゴリ選択、在庫設定）

## Shop 外部キー制約
- 1オーナー 1ショップ
- 1対1 : 外部キー制約(FK)

```
php artisan make:model Shop -m
```

- マイグレーション
```
$table->foreignId('owner_id')->constrained();
$table->string('name');
$table->text('information');
$table->string('filename');
$table->boolean('is_selling');
```

## ダミーデータ Seeder
DatabaseSeeder
外部キー制約がある場合は、
事前に必要なデータ（Owner）を設定する

```
$this->call([
    AdminSeeder::class,
    OwnerSeeder::class,
    ShopSeeder::class
]);
```

1. ダミーデータ作成
```
php artisan make:seed ShopSeeder
```

2. ダミーデータ編集
```php:umarche/database/seeders/ShopSeeder.php
 public function run(): void
    {
        DB::table('shops')->insert([
            [
                'owner_id' => 1,
                'name' => 'ここに店名が入ります',
                'information' => 'ここにお店の情報が入ります。ここにお店の情報が入ります。',
                'filename' => '',
                'is_selling' => true
            ],
            [
                'owner_id' => 2,
                'name' => 'ここに店名が入ります',
                'information' => 'ここにお店の情報が入ります。ここにお店の情報が入ります。',
                'filename' => '',
                'is_selling' => true
            ],
        ]);
    }
```

3. ダミーデータ追加
```php:umarche/database/seeders/DatabaseSeeder.php
$this->call([
    AdminSeeder::class,
    OwnerSeeder::class,
    ShopSeeder::class // 追加
]);
```

4. ダミーデータ反映
```
php artisan migrate:fresh --seed
```

## sec201 Shop リレーション 1対1
- オーナー：１つのショップで複数の商品を扱うことを想定

### Eloquent リレーション設定
- Owner
```
use App\Models\Shop;
public function shop()
{
    return $this->hasOne(Shop::class);
}
```

- Shop
```
use App\Models\Owner;
public function owner()
{
    return $this->belongsTo(Owner::class);
}
```

### Laravel Tinker で確認
```
php artisan tinker
```

```
$owner1 = App\Models\Owner::find(1)->shop;
// ... Ownerに紐づくShop情報を動的に取得
```

```
$shop1 = App\Models\Shop::find(1)->owner;
// ... Shopに紐づくOwner情報を動的に取得
```

1. 各モデルを編集する

```php:app/Models/Owner.php
public function shop()
    {
        return $this->hasOne(Shop::class);
    }
```

```php:app/Models/Shop.php
class Shop extends Model
{
    use HasFactory;

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
}
```

2. 紐づいているモデルが取得できるか確認する

```
php artisan tinker
```

```
> $owner1 = App\Models\Owner;;find(1);
> $owner1 = App\Models\Owner::find(1)->shop;
> $owner1 = App\Models\Owner::find(1)->shop->name;
> $owner1 = App\Models\Owner::find(1)->shop->is_selling;


> $owner1 = App\Models\Shop::find(1)->owner; 
> $owner1 = App\Models\Shop::find(1)->owner->name; 
> $owner1 = App\Models\Shop::find(1)->owner->email;  

quit
```
## sec202 Shopの作成　トランザクション
### Shopの作成
- Admin/OwnersController@store

- 外部キー向けにidを取得
```
$owner = Owner::create();
$owner->id;
```

-Shop::createで作成する場合はモデル側に $fillable も必要

### トランザクション
- 複数のテーブルに保存する際は
トランザクションをかける
無名関数内で親の変数を使うには use が必要
```
DB::transaction(function() use ($request){
    DB::create($request->name);
    DB::create($request->owner_id);
}, 2) //NG時2回試す
```

### 例外 + ログ
- トランザクションでエラー時は例外発生
PHP7から　　Throwableで例外取得
ログは strage/logs内に保存
```
use Throwable;
use Illuminate\Support\Facades\Log;

try {
    トランザクション処理
} catch( Throwable $e) {
    Log::error($e);
    throw $e;
}
```

## sec203 Shopの削除　カスケード
- Owner->shop と外部キー制約を設定しているため追加設定が必要。
```
$table->foreignId('owner_id')
    ->constrained()
    ->onUpdate('cascade')
    ->onDelete('cascade');
```
## sec204 Shop Index(Route, Controller, View)
### Shopの一覧/編集/更新
### リソース（Restful）コントローラ
- CRUD(新規作成、表示、更新、削除)
- C(create, store), R(index, show, edit),U(update),D(destroy)
表示・・GET、DBに保存・・POST

### Shop 表示までの設定
- Route
- index, edit, update の3つ
- owner.shop.index など

- View
- ロゴサイズ調整, Owner-navigation

- Controller・・ShopController
- __construct で$this->middleware('auth:owners');

- indexメソッド
```
Use illuminate\Support\Facades\Auth;
$ownerId = Auth::id(); // 認証されているid
$shops = Shop::where('owner_id', $ownerId)->get(); // whereは検索条件（「'owner_id'」を「ログインしている「$ownerId」で検索しつつgetで取得すれば、ログインしている「$ownerId」が入ったShopだけが取得できる）
```

1. routes/owner.php を編集
```
use App\Http\Controllers\Owner\ShopController;

Route::prefix('shop')->
middleware('auth:owners')->group(
    function(){
        Route::get('index', [ShopController::class, 'index'])->name('shops.index');
        Route::get('edit/{shop}', [ShopController::class, 'edit'])->name('shops.edit');
        Route::post('update/{shop}', [ShopController::class, 'update'])->name('shops.update');
    }
);
```

2. shopコントローラを作成
```
php artisan make:controller Owner/ShopController
```

3. shopコントローラ編集
```
<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
Use illuminate\Support\Facades\Auth;
use App\Models\Shop;

class ShopController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:owners');
    }

    public function index()
    {
        $ownerId = Auth::id();
        $shops = Shop::where('owner_id', $ownerId)->get();

        return view('owner.shops.index',
        compact('shops'));
    }

    public function edit(string $id)
    {
    }

    public function update(Request $request, string $id)
    {
    }
}
```

4. View側のファイル作成
```php:resources/views/owner/shops/edit.blade.php
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
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

```

```php:resources/views/owner/shops/index.blade.php
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
                    @foreach ($shops as $shop)
                        {{ $shop->name }}
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

```

```php:resources/views/layouts/owner-navigation.blade.php
<!-- Navigation Links -->
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    <x-nav-link :href="route('owner.dashboard')" :active="request()->routeIs('owner.dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>
    <x-nav-link :href="route('owner.shops.index')" :active="request()->routeIs('owner.shops.index')">
        店舗情報
    </x-nav-link>
</div>

<!-- Responsive Navigation Menu -->
<div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
    <div class="pt-2 pb-3 space-y-1">
        <x-responsive-nav-link :href="route('owner.dashboard')" :active="request()->routeIs('owner.dashboard')">
            {{ __('Dashboard') }}
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('owner.shops.index')" :active="request()->routeIs('owner.shops.index')">
            店舗情報
        </x-responsive-nav-link>
    </div>
```

## sec205 Shop コントローラ　ミドルウェア
### Shop ルートパラメータの注意
- /owner/shops/edit/2/

- edit, update などURLにパラメータを使う場合
URLの数値を直接変更すると
他のオーナーのShopが見れてしまう。
→ログイン済みオーナーのShop URL出なければ404を表示

### Shop ミドルウェア設定
- コンストラクタ内
```
$this->middleware(function($request, $next) {
    $id = $request->route()->parameter('shop'); // shopのid取得
    if(!is_null($id)) { //null判定
        $shopsOwnerId = Shop::findOrFail($id)->owner->id;
        $shopId = (int)$shopsOwnerId; // キャスト　文字列→数値に型変換
        $ownerId = Auth::id();
        if($shopId !== $ownerId) { // 同じでなかったら
            abort(404); // 404画面表示
        }
    }
    return $next($request);
});
```

## sec206 Shop カスタムHTTPエラーページ
- Laravelのデフォルトのエラーページテンプレートデザインをカスタムする
```
php artisan vendor:publish --tag=laravel-errors
```

- リソースのviewsに新しくerrorsというディレクトリが作成され、エラー毎のファイルが吐き出されている。
- minimalだったり、layoutだったり、それぞれ表示させたいデザインによって
パスの指定をしたりデザインをカスタムすることが可能

## sec207 Shop Index画面
### Shop Index画面
- 初期設定 NO IMAGE画像

- 無料画像サイト
https://pixabay.com/ja/


## sec208 画像アップロード
### 画像アップロード
- バリデーション->〇〇
- 画像サイズ（1920px × 1080px (FullHD)）
-> ①ユーザ側でリサイズしてもらう
-> ②サーバー側でリサイズする
 -> Intervention Imageを使う
- 重複しないファイル名にて変更、保存

### 画像アップロード　ビュー側
- View側
```
<form method="post" action="" enctype="multipart/form-data">

<input type="file" accept="image/png, image/jpeg, image/jpg">
```

### 画像アップロード　コントローラ側
- リサイズしないパターン（putFileでファイル名生成）
```
use Illuminate\Support\Facades\Storage;

public function update(Request $request, $id)
    {
        $imageFile = $request->image;
        if(!is_null($imageFile) && $imageFile->isValid() ){
            Storage::putFile('public/shops', $imageFile);
        }
    }
```

## sec209 フォームリクエスト（カスタムリクエスト）
### フォームリクエスト　１
```
php  artisan make:request UploadImageRequest
```

- App\Http\Requests\UploadImageRequest.php が生成

```
public function authorize()
{
    return true;
}

public function rules()
{
    return [
        'image'=>'image|mines:jpg,jpeg,png|max:2048',
    ];
}
```

### フォームリクエスト 2
```
public function messages()
{
    return [
        'image' => '指定されたファイルが画像ではありません。',
        'mines' => '指定された拡張子(jpg/jpeg/png)ではありません。',
        'max' => 'ファイルサイズは2MB以内にしてください。',
    ];
}
```
