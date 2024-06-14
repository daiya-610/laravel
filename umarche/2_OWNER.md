# オーナー概要
## オーナーでできること
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