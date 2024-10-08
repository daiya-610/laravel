# コマンド集

# 1.<u>作成系</u>
## 1-1\.プロジェクトを作成
```
composer create-project laravel/laravel sample
```
## 1-2\.コントローラーの作成
```
php artisan make:controller SampleController
php artisan make:controller SampleController --invokable  //single actionコントローラー
php artisan make:controller SampleController --resource   //resourceコントローラー
```
### 1-2-1\.階層を分けて作成
```
php artisan make:controller SampleLayered\\SampleController --model=Sample
```

## 1-3\.モデルの作成
```
php artisan make:model Sample
php artisan make:model Sample --factory    //factoryファイルも生成される
php artisan make:model Sample --migration  //migrationファイルも生成される
php artisan make:model Sample --policy     //policyファイルも生成される
php artisan make:model Sample --seed       //seederファイルも生成される
php artisan make:model Sample --resource   //resourceコントローラーも生成される
php artisan make:model Sample --all        //上記ファイルに加えFormRequestファイルも生成される
```

## 1-4\.マイグレーションの作成
```
php artisan make:migration create_sample_table
```

## 1-5\.フォームリクエストクラスの作成
```
php artisan make:request SampleRequest
```

## 1-6\.独自ルールの作成
```
php artisan make:rule SampleRule
```

## 1-7\.Seeder（シーダー）の作成
```
php artisan make:seeder SampleSeeder
```

## 1-8\.テストの作成
```
php artisan make:test SampleTest         // Feature配下に生成される
php artisan make:test SampleTest --unit  // Unit配下に生成される
```

## 1-9\.リソースの作成
```
php artisan make:resource SampleResource
```

## 1-10\.メール - Mailableクラスの作成（メール送信が行われるルーティングの作成）
```
php artisan make:mail SampleMail
```

## 1-11\.ミドルウェアの作成
```
php artisan make:middleware SampleMiddleware
```

## 1-12\.コマンドの作成
```
php artisan make:command SampleCommand
```

## 1-13\.ポリシーの作成
```
php artisan make:policy SamplePolicy
```

## 1-14\.ファクトリーの作成
```
php artisan make:factory SampleFactory
```

## 1-15\.ジョブの作成
```
php artisan make:job SampleJob
```
*****

## 1-16\.APP_KEYの生成 - .envファイルのAPP_KEYの項目が変更される。
- 不用意に変更すると、DBなどに保存されたパスワードの照合などができなくなるので注意
```
php artisan key:generate
```

## 1-17\.シンボリックリンクの作成
- デフォルトでstrageはweb側から参照できない→シンボリックリンクを貼る。
- Laravelの仕様上、そうしないとweb側からstorage下のファイルにアクセスできない、ファイル規約があるため。
```
php artisan storage:link
```

## 1-18\.stabファイル生成
```
php artisan stub:publish
```

## 1-19\.langディレクトリ作成
```
php artisan lang:publish
```

*****
*****

# 2.<u>実行系</u>
## 2-1\.tinker起動
#### 用途
- テストデータを作るときFakerの値を確認したい時
- Laravel tinker で Faker を試す方法
- 試しにコントローラを動かしたい時
- Laravel コントローラをtinkerから実行する
- EloquentのSQLの実行結果を見たい時<br>
<a href="https://qiita.com/ucan-lab/items/753cb9d3e4ceeb245341">参照：Laravel SQLの実行クエリログを出力する</a>

- 実行時のconfigの値を確認したい時
- → ちょっとした確認の時に使用
```
php artisan tinker
```

## 2-2\.マイグレーション実行
```
php artisan migrate
php artisan migrate:rollback  // 最後のmigrationをロールバック
php artisan migrate:reset     // 全てのmigrationをロールバック
php artisan migrate:refresh   // 全てのmigrationをロールバック後migrationを実行
php artisan migrate:fresh     // 全てのテーブルを削除後migrationを実行
php artisan migrate:fresh --seed     // 全てのテーブルを削除後migrationを実行し、Seeder実行
```

## 2-3\.Seeder（シーダー）の実行
```
php artisan db:seed
php artisan db:seed --class SampleSeeder  // 特定のSeederを指定して実行
```

## 2-4\.キューの実行
- キュー:ある決まった処理を非同期で実行するための仕組み
- キューにジョブを登録していきその処理を実行していく
```
artisan queue:work
artisan queue:work --queue=queueName  // 名前付きqueueを実行
```

## 2-5\.スケジュールをローカルで実行
```
php artisan schedule:work
```

## 2-6\.テストの実行
```
php artisan test                             // 全てのテスト実行
php artisan test tests/Unit/SampleTest.php   // ファイルを指定して実行
```

## 2-7\.ファイルの自動読み込み
- requireなんとかを使用せずにどこからでも対象のソースやクラスの呼び出しが可能
- Vender配下のとあるファイルに全ての*.phpファイルのパスが自動的に記述される
- →クラスを増やした、ファイルを増やした、変更した場合、ソースが無いってエラーになった場合に使用。
```
composer dump-autoload
```

## 2-8\.最適化（ファイルのリネームをしたり手動でControllerなどを作成した際）
```
php artisan optimize
```

## 2-9\.キャッシュクリア
```
php artisan cache:clear
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan view:clear
```

## 2-10\.vender publish
- 製作中のWebアプリケーションに、ライブラリの設定ファイルなどをコピーする
```
php artisan vendor:publish --tag=laravel-errors      // エラー用のblade生成(404など)
php artisan vendor:publish --tag=laravel-pagination  // ページネーションデザインのblade生成
php artisan vendor:publish --tag=laravel-mail        // メールテンプレートのblade生成
php artisan vendor:publish --tag=sanctum-migrations  // sanctum用のmigration生成
```
*****
*****
# 3.<u>確認系</u>
## 3-1\.ルート確認
```
php artisan route:list
```

```
php artisan route:list | grep admin.
```

---
---
# Laravel 構文関係
## Componentのパターン
- 1つのコンポーネント（部品）を複数ページで使用可能
- コンポーネント側を修正すると全て反映される。

## Componentの書き方
- resources/views/components フォルダ内に配置
- <x-コンポーネント名></x-コンポーネント名>
- フォルダで分けたい場合
- resources/views/components/tests フォルダの場合
<x-tests.コンポーネント名></x-tests.コンポーネント名>

## Slotの使い方
- slotを使用する場合、component側で「{{ $slot }}」という変数を付けることで使用できる。
- マスタッシュ構文：波括弧を2つ付ける構文「{{}}」 
<!-- マスタッシュ：口ひげという意味 -->
```php
// Component側
{{ $slot }}

// Blade側
<x-app>テストテスト</x-app>
```

### 名前付きSlot
- Slotを複数使用する場合
```php
// Component側
{{ $header }}

// Blade側
<x-slot name="header">テストテスト</x-slot>
```
