## 概要

Week17「パフォーマンス計測と改善」の対応です。Lighthouse で計測し、N+1・スロークエリ・キャッシュ・画像・フロントエンドを改善しました。

## Lighthouse（モバイル）

| ページ | | Performance | Accessibility | Best Practices | SEO |
|---|---|---|---|---|---|
| `/posts` | before | 54 | 83 | 100 | 82 |
| | after | **99** | **100** | **100** | **100** |
| `/threads` | before | 46 | 84 | 100 | 82 |
| | after | **99** | **100** | **100** | **100** |

レポート: `docs/lighthouse/`（after は本番用イメージ `docker-compose.perf.yml` で計測）

## 練習課題1: N+1 問題（クエリ数の before / after）

計測条件: 投稿 20,000件・スレッド 2,000件・レス 20,000件（`PerformanceSeeder`）。セッションの読み書き（2クエリ）は除く。

| 一覧 | 書き方 | クエリ数 | DB時間 |
|---|---|---|---|
| `/posts`（10件） | before: eager loading なし（N+1） | 12 | 16.1 ms |
| | after: `with('user')` | **3** | **7.7 ms** |
| `/threads`（20件） | before: eager loading なし（N+1） | 42 | 52.0 ms |
| | before: `with(['user', 'replies'])`（変更前のコード） | 4（レス本文 197行を読み込み） | 7.7 ms |
| | after: `with('user')->withCount('replies')` | **3** | **4.5 ms** |

- 投稿・掲示板は Week8 で `with()` を導入済みだったため、eager loading の有無で比較した
- `/threads` はレス「数」を表示するだけなのに全レス本文を読み込んでいたので `withCount()` に変更
- `Model::preventLazyLoading()` を有効化（本番以外）し、今後 N+1 を書くと開発・テスト中に例外で気付けるようにした

## スロークエリ（インデックス）

`select * from posts order by created_at desc limit 10`

| | 実行計画 | 読んだ行数 | 時間 |
|---|---|---|---|
| before | Table scan → Sort | 20,000 | 69.1 ms |
| after | Index scan（posts_created_at_index） | 10 | **0.27 ms** |

posts / threads / products の `created_at`、replies の `(thread_id, created_at)` にインデックスを追加。

## 練習課題2: キャッシュ（投稿一覧）

`PostRepository::getPublished()`（画面と API の両方で使用）を Redis にキャッシュ。
投稿の作成・更新・削除時は Post モデルのイベントでキャッシュの世代番号を進め、全ページのキャッシュを無効化する。

| | キャッシュなし | キャッシュあり（Redis） |
|---|---|---|
| 1ページ目の一覧取得 | 8.23 ms / 3クエリ | **0.94 ms / 0クエリ** |
| 500ページ目の一覧取得 | 189.69 ms / 3クエリ | **1.17 ms / 0クエリ** |
| HTTP `/posts?page=500`（中央値） | 458 ms | **270 ms** |

※ HTTP の時間には Docker Desktop（Windows）の固定オーバーヘッド約230ms が含まれる

## 練習課題3: 画像の WebP 変換・CDN 配信

- `App\Services\ImageOptimizer`: アップロード画像を WebP（品質80）に変換し、横幅 1200px を超える場合は縮小
- S3 に `Content-Type: image/webp`、`Cache-Control: public, max-age=31536000, immutable` を付けて保存
- `AWS_URL` に CloudFront のドメインを設定すると、画像 URL が CloudFront 経由になる（設定手順は README）

| 元画像 | 元サイズ | WebP | 削減率 |
|---|---|---|---|
| 写真 3000×2000 JPEG | 950.5 KB | 195.0 KB（1200×800） | 79% |
| 写真 1600×1067 JPEG | 404.4 KB | 244.0 KB（1200×800） | 40% |
| 同じ写真の PNG | 3,392.3 KB | 244.0 KB | 93% |

## その他

- Tailwind CDN → Vite ビルドに変更、Nginx の gzip・静的ファイルのキャッシュ設定、アクセシビリティ修正
- phpredis と GD（JPEG / WebP 対応）を Dockerfile に追加
- **テストが開発用 MySQL に接続していた問題を修正**（`phpunit.xml` に `<server>` を追加）

## テスト

- PHPUnit: 全件成功（キャッシュ 4件・画像変換 7件のテストを追加）
- PHP CS Fixer / PHPStan: エラーなし

🤖 Generated with [Claude Code](https://claude.com/claude-code)
