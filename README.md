# techmeets-month2 / Week8

## 概要

Laravel + Docker + Laravel Breeze で構築した会員制Webアプリです。
ユーザー登録・ログイン機能を持ち、ブログ投稿と掲示板（スレッド＋レス）の2つの機能を提供します。
未ログインでも閲覧できますが、投稿・編集・削除はログインが必要です。

## 使用技術

- PHP 8.2
- Laravel 12
- Laravel Breeze（認証機能）
- MySQL 8.0
- Nginx
- Docker / Docker Compose
- Tailwind CSS（CDN）

## セットアップ手順

```bash
# 1. コンテナを起動
docker-compose up -d

# 2. 依存パッケージをインストール
docker-compose exec app composer install

# 3. 環境変数ファイルを作成
cp .env.example .env
docker-compose exec app php artisan key:generate

# 4. データベースのテーブルを作成
docker-compose exec app php artisan migrate
```

ブラウザで http://localhost にアクセスして確認できます。

## 機能一覧

### 認証（Laravel Breeze）
- ユーザー登録
- ログイン / ログアウト
- パスワードリセット
- プロフィール編集

### 掲示板
- スレッド一覧表示（ページネーション付き）
- スレッド詳細・レス一覧表示
- スレッド作成（ログイン必須）
- レス投稿（ログイン必須）
- 自分のスレッド・レスのみ削除可能

### ブログ
- 投稿一覧表示（ページネーション付き）
- 投稿詳細表示
- 投稿作成（タイトル・内容・カテゴリー、ログイン必須）
- 投稿編集・削除（自分の投稿のみ）
- バリデーション実装

### 商品管理
- 商品一覧・詳細表示
- 商品登録・編集・削除（CRUD）
- バリデーション実装（価格・在庫は0以上の整数）

## セキュリティ対策

| 対策 | 実装方法 |
|---|---|
| CSRF対策 | フォームに `@csrf` |
| XSS対策 | Blade の `{{ }}` による自動エスケープ |
| 認証ガード | `Route::middleware('auth')` |
| 認可（所有権チェック） | `isOwnedBy()` メソッドで自分のデータのみ操作可能 |
| マスアサインメント対策 | モデルの `$fillable` で許可カラムを明示 |
| バリデーション | `$request->validate()` で入力値を検証 |

## URL一覧

| URL | 説明 |
|---|---|
| `http://localhost` | トップ（掲示板一覧）|
| `http://localhost/threads` | 掲示板一覧 |
| `http://localhost/posts` | ブログ一覧 |
| `http://localhost/products` | 商品管理一覧 |
| `http://localhost/register` | ユーザー登録 |
| `http://localhost/login` | ログイン |
| `http://localhost:8080` | phpMyAdmin |
