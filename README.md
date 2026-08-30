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

## AWSデプロイ手順

### 1. EC2インスタンスの作成
- AMI: Ubuntu Server（要件は22.04 LTS）
- インスタンスタイプ: t2.micro
- キーペアを作成し、`.pem`ファイルを安全な場所（`~/.ssh`など）に保管

### 2. セキュリティグループの設定
下記「セキュリティグループの設計理由」を参照。SSHは自分のIPのみ、HTTPは全許可で作成する。

### 3. SSH接続
```bash
ssh -i "キーファイルのパス.pem" ubuntu@<EC2のパブリックIP>
```

### 4. Dockerのインストール
```bash
sudo apt-get update
sudo apt-get install -y ca-certificates curl gnupg
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo $VERSION_CODENAME) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo usermod -aG docker ubuntu
```

### 5. アプリのクローンと起動
```bash
git clone https://github.com/garrcppmad-boop/techmeets-month2.git app
cd app
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

### 6. RDS（MySQL）への接続設定
EC2上のMySQLコンテナの代わりにRDSを使う場合は、`.env`の`DB_*`をRDSのエンドポイントに向けてマイグレーションを実行する。
```
DB_CONNECTION=mysql
DB_HOST=<RDSエンドポイント>.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=<RDSユーザー名>
DB_PASSWORD=<RDSパスワード>
```
```bash
docker compose exec app php artisan migrate
```

### 7. 動作確認
ブラウザで `http://<EC2のパブリックIP>` にアクセスし、アプリが表示されることを確認する。

## セキュリティグループの設計理由

「とりあえず開けた」ルールは無く、それぞれ用途に基づいて許可範囲を最小化している。

| ポート | プロトコル | 許可元 | 理由 |
|---|---|---|---|
| 22 (SSH) | TCP | `<自分のグローバルIP>/32` | サーバーの管理（ログイン・デプロイ作業）を行うのは自分の端末のみのため、許可元を自分のグローバルIP1つに限定した。`0.0.0.0/0`にすると世界中からSSHの総当たり攻撃・鍵の推測攻撃を受け続けることになり、攻撃対象領域（アタックサーフェス）が不必要に広がるため避けた。 |
| 80 (HTTP) | TCP | `0.0.0.0/0` | このアプリは不特定多数のユーザーがブラウザからアクセスするWebアプリであり、閲覧者のIPアドレスを事前に特定できない。そのため、公開Webサービスとして機能させるにはHTTPは全世界に開放する必要がある。 |
| 3306 (MySQL) | TCP | 未開放（インターネットからは許可しない） | データベースはアプリケーション（EC2内のコンテナ、または同一VPC内）からのみ参照できればよく、外部に直接公開する理由がない。ここを開けるとDBへの不正アクセス・データ漏洩リスクに直結するため、意図的に許可ルールを作らなかった。 |

> 補足: SSHの許可元IP（`<自分のグローバルIP>/32`）は自宅・作業環境のグローバルIPが変わるたびに更新が必要。固定できない環境の場合はVPN経由での接続や踏み台サーバーの導入を検討する。
