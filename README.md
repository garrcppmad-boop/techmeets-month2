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
- Stripe（stripe/stripe-php・Checkout / Webhook）
- SendGrid（SMTP リレーでメール送信）

## セットアップ手順（ローカル開発）

```bash
# 1. コンテナを起動（docker-compose.yml と docker-compose.override.yml が自動マージされる）
docker compose up -d

# 2. 依存パッケージをインストール
docker compose exec app composer install

# 3. 環境変数ファイルを作成
cp .env.example .env
docker compose exec app php artisan key:generate

# 4. データベースのテーブルを作成
docker compose exec app php artisan migrate
```

ブラウザで http://localhost にアクセスして確認できます。

### 開発環境と本番環境の構成

| | 起動コマンド | DB | 起動するコンテナ |
|---|---|---|---|
| 開発 | `docker compose up -d` | MySQL コンテナ（`db`） | nginx / app / db / phpmyadmin |
| 本番 | `docker compose -f docker-compose.yml up -d` | RDS（`.env` の `DB_*`） | nginx / app |

- `docker-compose.yml` … 本番でも使う最小構成（nginx + app）。app に DB 接続情報は書かず、Laravel が `.env` の `DB_*` を読む。
- `docker-compose.override.yml` … ローカル開発用。`docker compose` が自動でマージし、`db` / `phpmyadmin` を追加して app の `DB_*` を MySQL コンテナへ向ける。
- 本番では `-f docker-compose.yml` を明示して override を無効化するため、`.env` の RDS 設定がそのまま有効になる。

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

### 決済（Stripe Checkout）
- `/checkout` に購入可能な商品一覧を表示（ログイン必須）
- 「購入する」→ `CheckoutController@checkout` が `Stripe\Checkout\Session::create()` で
  Checkout セッションを作成し、Stripe のホスト決済ページへリダイレクト
- セッションの `payment_intent_data.metadata` に `user_id` / `product_id` を載せ、
  Webフック側で購入履歴（`purchases` テーブル）に紐付ける
- 決済完了 Webフック（`POST /api/webhook/stripe`）
  - `Stripe\Webhook::constructEvent()` で署名検証（`STRIPE_WEBHOOK_SECRET`）
  - `payment_intent.succeeded` を受信したら決済ID（PaymentIntent ID）をログに記録し、
    `Purchase::updateOrCreate()` で冪等に購入履歴を保存

#### ローカルでの決済テスト
```bash
# 1. Webフックをローカルへ転送（表示される whsec_... を .env の STRIPE_WEBHOOK_SECRET に設定）
stripe listen --forward-to localhost/api/webhook/stripe
docker compose exec app php artisan config:clear

# 2. ブラウザで /checkout → 購入する → テストカード 4242 4242 4242 4242 で決済
# 3. ログを確認
docker compose exec app tail -f storage/logs/laravel.log
```

### メール送信（SendGrid）
- 会員登録完了（`Registered` イベント）→ `App\Listeners\SendWelcomeEmail` が
  `App\Mail\WelcomeMail` を送信（ウェルカムメール）
- 送信経路は **SendGrid の SMTP リレー**。`.env` の設定は次のとおり:

| キー | 値 | 補足 |
|---|---|---|
| `MAIL_MAILER` | `smtp` | 実送信しない場合は `log` |
| `MAIL_HOST` | `smtp.sendgrid.net` | |
| `MAIL_PORT` | `587` | STARTTLS |
| `MAIL_USERNAME` | `apikey` | SendGrid の仕様で固定文字列 |
| `MAIL_PASSWORD` | `${SENDGRID_API_KEY}` | `SENDGRID_API_KEY` を参照 |
| `SENDGRID_API_KEY` | `SG.xxxx` | SendGrid で発行した APIキー |
| `MAIL_FROM_ADDRESS` | 認証済み送信元 | SendGrid の Single Sender Verification で認証したアドレス |

- 送信確認は SendGrid 管理画面の **Activity Feed**（`Processed` → `Delivered`）で行う

## セキュリティ対策

| 対策 | 実装方法 |
|---|---|
| CSRF対策 | フォームに `@csrf` |
| XSS対策 | Blade の `{{ }}` による自動エスケープ |
| 認証ガード | `Route::middleware('auth')` |
| 認可（所有権チェック） | `isOwnedBy()` メソッドで自分のデータのみ操作可能 |
| マスアサインメント対策 | モデルの `$fillable` で許可カラムを明示 |
| バリデーション | `$request->validate()` で入力値を検証 |
| Webフックの改ざん検証 | `Stripe\Webhook::constructEvent()` で署名を検証し、不正なら 400 |
| 秘密情報の管理 | APIキー等は `.env`（Git 管理外）。`.env.example` はプレースホルダのみ |

## URL一覧

| URL | 説明 |
|---|---|
| `http://localhost` | トップ（掲示板一覧）|
| `http://localhost/threads` | 掲示板一覧 |
| `http://localhost/posts` | ブログ一覧 |
| `http://localhost/products` | 商品管理一覧 |
| `http://localhost/checkout` | 商品購入（Stripe Checkout、ログイン必須）|
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

### 5. アプリのクローンと起動（本番構成）
本番では `-f docker-compose.yml` を明示し、開発用の override（DBコンテナ）を読み込ませない。
```bash
git clone https://github.com/garrcppmad-boop/techmeets-month2.git app
cd app
cp .env.example .env      # 次の手順6で DB_* を RDS に書き換える
docker compose -f docker-compose.yml up -d
docker compose -f docker-compose.yml exec app composer install
docker compose -f docker-compose.yml exec app php artisan key:generate
```

### 6. RDS（MySQL）への接続設定
本番構成（手順5）では `db` コンテナは起動せず、app は `.env` の `DB_*` をそのまま使う。
`.env` を RDS のエンドポイントに向けてからマイグレーションする。
```
DB_CONNECTION=mysql
DB_HOST=<RDSエンドポイント>.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=<RDSユーザー名>
DB_PASSWORD=<RDSパスワード>
```
```bash
docker compose -f docker-compose.yml exec app php artisan config:clear
docker compose -f docker-compose.yml exec app php artisan migrate --force
```

> RDS のセキュリティグループは、EC2 のセキュリティグループからの 3306 のみを許可する
> （`0.0.0.0/0` では開けない）。

### 7. 動作確認
ブラウザで `http://<EC2のパブリックIP>` にアクセスし、アプリが表示されることを確認する。

## 独自ドメイン + HTTPS 化（Let's Encrypt）

`http://<Elastic IP>` で動いている状態を前提に、独自ドメインを割り当てて HTTPS 公開する。
構成は **ホスト Nginx（80/443・TLS 終端）→ Docker コンテナ内 Nginx（127.0.0.1:8080）→ PHP-FPM**。

### 1. ドメイン取得と A レコード設定
1. ドメイン取得サービス（お名前.com / Route 53 / Cloudflare Registrar 等、年 1,000〜1,500 円）で独自ドメインを取得
2. DNS 管理画面で A レコードを設定
   - `example.com`      → `<Elastic IP>`
   - `www.example.com`  → `<Elastic IP>`
3. 反映を確認（下記「練習課題1」の `dig`）。返ってこない場合は数分〜数時間待つ

### 2. セキュリティグループに 443 を追加
インバウンドに `443 (HTTPS) / TCP / 0.0.0.0/0` を追加する（80 は Week11 で追加済み）。
80 も開けたままにする（Certbot の HTTP-01 チャレンジが 80 を使う）。

### 3. コンテナ Nginx を内部公開に変更
`docker-compose.yml` の nginx サービスを次のように変更し、`docker compose -f docker-compose.yml up -d` で反映。

```yaml
  nginx:
    ports:
      - "127.0.0.1:8080:80"   # ← "80:80" から変更。ホスト Nginx からのみ到達可能にする
```

### 4. ホスト Nginx を設定（リバースプロキシ）
```bash
sudo apt update && sudo apt install -y nginx
sudo mkdir -p /var/www/certbot

# リポジトリの deploy/nginx/laravel-app.conf を配置し、example.com を自分のドメインに全置換
sudo cp app/deploy/nginx/laravel-app.conf /etc/nginx/sites-available/laravel-app.conf
sudo sed -i 's/example\.com/取得したドメイン/g' /etc/nginx/sites-available/laravel-app.conf
sudo ln -s /etc/nginx/sites-available/laravel-app.conf /etc/nginx/sites-enabled/laravel-app.conf
sudo rm -f /etc/nginx/sites-enabled/default

sudo nginx -t && sudo systemctl reload nginx
# この時点で http://ドメイン がアプリを表示すれば STEP A 成功
```

### 5. Certbot で証明書取得
```bash
sudo apt install -y certbot
sudo certbot certonly --webroot -w /var/www/certbot \
  -d 取得したドメイン -d www.取得したドメイン \
  --email <自分のメール> --agree-tos --no-eff-email
```

### 6. HTTPS + リダイレクトを有効化
`/etc/nginx/sites-available/laravel-app.conf` を編集：
- STEP A の `location / { proxy_pass ... }` を削除し、`return 301 https://ドメイン$request_uri;` を有効化
- STEP B の 2 つの `server { listen 443 ssl; ... }` ブロックのコメントを外す

```bash
sudo nginx -t && sudo systemctl reload nginx
```

### 7. Laravel 側の設定（プロキシ配下の HTTPS 対応）
TLS はホスト Nginx で終端し、コンテナには HTTP で渡るため、そのままだと Laravel が
`http://` の URL を生成して CSS/JS が混在コンテンツになる。`.env`：

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://取得したドメイン
SESSION_SECURE_COOKIE=true
```

`bootstrap/app.php`（Laravel 12）に信頼プロキシを追加：
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: '*');   // ホスト Nginx からの X-Forwarded-* を信頼
})
```

```bash
docker compose -f docker-compose.yml exec app php artisan config:cache
docker compose -f docker-compose.yml exec app php artisan route:cache
```

### 8. 証明書の自動更新
```bash
sudo systemctl list-timers | grep certbot     # snap/apt 版とも timer が自動登録される
sudo certbot renew --dry-run                   # 更新シミュレーション
```
更新後に Nginx へ反映するため deploy hook を設定：
```bash
echo -e '#!/bin/sh\nsystemctl reload nginx' | sudo tee /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh
sudo chmod +x /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh
```

---

## 練習課題1：コマンドで動作確認する

実際に取得したドメイン `sk-week13.com` に対して実行した結果。

### `dig sk-week13.com +short`
```
13.238.87.91
```
権威 DNS に問い合わせた最終的な A レコード（IP アドレス）だけを表示する。
EC2 の Elastic IP（`13.238.87.91`）が返っており、「ドメイン → サーバー」の名前解決が
正しく設定できていることを意味する。

### `curl -I https://sk-week13.com`
```
HTTP/1.1 302 Found
Server: nginx/1.24.0 (Ubuntu)
Content-Type: text/html; charset=utf-8
X-Powered-By: PHP/8.2.34
Location: https://sk-week13.com/threads
```
`-I` は HTTP レスポンスヘッダーのみを取得する。TLS ハンドシェイクが成功した時点で
証明書が有効に読めている証拠（失敗していれば `curl` 自体がエラーで止まる）。
`302 Found` は Laravel 側のルーティングによるリダイレクト（`/` → `/threads`）で、
HTTPS 配信自体は成功している。`Server: nginx` がホスト Nginx、`X-Powered-By: PHP` が
出ていることからコンテナ内の PHP-FPM まで到達していることが分かる。

### `sudo certbot certificates`
```
Certificate Name: sk-week13.com
  Domains: sk-week13.com www.sk-week13.com
  Expiry Date: 2027-01-02 21:53:14+00:00 (VALID: 89 days)
  Certificate Path: /etc/letsencrypt/live/sk-week13.com/fullchain.pem
```
インストール済み証明書の一覧と **有効期限（Let's Encrypt は発行から90日）** を表示する。
`VALID: 89 days` が残日数。自動更新の設定自体は systemd timer 側
（`systemctl list-timers | grep certbot`）で管理され、期限 30 日前を切ると
`certbot renew` が自動更新する。

## 練習課題2：www なし・あり を統一する

`www.sk-week13.com` へのアクセスも `https://sk-week13.com`（www なし）へ 301 リダイレクト
する。`deploy/nginx/laravel-app.conf` の `server_name www.sk-week13.com` のブロックが
これを担当する：

```nginx
server {
    listen 443 ssl;
    server_name www.sk-week13.com;
    ssl_certificate     /etc/letsencrypt/live/sk-week13.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/sk-week13.com/privkey.pem;
    return 301 https://sk-week13.com$request_uri;   # www なしへ集約
}
```
証明書は `-d sk-week13.com -d www.sk-week13.com` の2つを1枚に含めているため、www でも
TLS ハンドシェイクは成功し、その上で301で非wwwに寄せられる。

確認結果：
```
$ curl -I https://www.sk-week13.com
HTTP/1.1 301 Moved Permanently
Location: https://sk-week13.com/
```
想定どおり `301` で非 www のドメインへリダイレクトされている。

### 8. 使い終わったAWSリソースの停止・削除（課金防止）

検証が終わったら**必ず**リソースを片付ける。放置すると無料枠超過や Elastic IP などで課金が発生する。

#### 一時的に止めるだけ（あとで再開する場合）
| リソース | 操作 | 課金の扱い |
|---|---|---|
| EC2 | インスタンスを「停止(Stop)」 | インスタンス料金は止まる。ただし **EBS ボリューム**と、割り当て済みで**未使用の Elastic IP** は課金され続ける |
| RDS | 「一時停止(Stop temporarily)」 | 最大7日間停止可能。ストレージ料金は継続。7日後に自動再開 |

#### 完全に削除する（もう使わない場合）— 推奨順序
1. **EC2 インスタンスを終了(Terminate)**
   - EC2 コンソール → インスタンス → 対象を選択 → インスタンスの状態 → 「インスタンスを終了」
   - 「終了時に削除」が有効な EBS ボリュームは一緒に消える
2. **EBS ボリュームの残りを確認・削除**
   - EC2 コンソール → Elastic Block Store → ボリューム → `available`（未アタッチ）状態のものを削除
3. **Elastic IP の解放(Release)**
   - EC2 コンソール → Elastic IP → 対象を選択 → アクション → 「Elastic IP アドレスの解放」
   - ※ 関連付け先の EC2 を終了しただけでは解放されない。未関連付けの Elastic IP は課金対象なので必ず解放する
4. **RDS インスタンスを削除(Delete)**
   - RDS コンソール → データベース → 対象 → アクション → 削除
   - 「最終スナップショットの作成」不要ならチェックを外す（スナップショットも保存料金がかかる）
   - 「自動バックアップの保持」も不要なら削除
5. **RDS の手動スナップショットを削除**
   - RDS コンソール → スナップショット → 残っているものを削除
6. **その他**
   - CloudWatch のログ グループ（`/aws/rds/...` など）が残っていれば削除
   - セキュリティグループ・キーペア・VPC 自体は課金されないが、不要なら整理する

#### 片付け後の確認
- EC2: 実行中インスタンス 0
- EC2 → Elastic IP: 一覧が空
- EC2 → ボリューム: 一覧が空（または不要な `available` が無い）
- RDS: データベース・スナップショットが空
- 請求(Billing) → 無料利用枠 / コストエクスプローラーで当日の課金が増えていないこと

## セキュリティグループの設計理由

「とりあえず開けた」ルールは無く、それぞれ用途に基づいて許可範囲を最小化している。

| ポート | プロトコル | 許可元 | 理由 |
|---|---|---|---|
| 22 (SSH) | TCP | `<自分のグローバルIP>/32` | サーバーの管理（ログイン・デプロイ作業）を行うのは自分の端末のみのため、許可元を自分のグローバルIP1つに限定した。`0.0.0.0/0`にすると世界中からSSHの総当たり攻撃・鍵の推測攻撃を受け続けることになり、攻撃対象領域（アタックサーフェス）が不必要に広がるため避けた。 |
| 80 (HTTP) | TCP | `0.0.0.0/0` | このアプリは不特定多数のユーザーがブラウザからアクセスするWebアプリであり、閲覧者のIPアドレスを事前に特定できない。そのため、公開Webサービスとして機能させるにはHTTPは全世界に開放する必要がある。 |
| 3306 (MySQL) | TCP | 未開放（インターネットからは許可しない） | データベースはアプリケーション（EC2内のコンテナ、または同一VPC内）からのみ参照できればよく、外部に直接公開する理由がない。ここを開けるとDBへの不正アクセス・データ漏洩リスクに直結するため、意図的に許可ルールを作らなかった。 |

> 補足: SSHの許可元IP（`<自分のグローバルIP>/32`）は自宅・作業環境のグローバルIPが変わるたびに更新が必要。固定できない環境の場合はVPN経由での接続や踏み台サーバーの導入を検討する。
