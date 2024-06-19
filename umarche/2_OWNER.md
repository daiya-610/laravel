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