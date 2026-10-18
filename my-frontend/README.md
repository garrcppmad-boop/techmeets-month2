# my-frontend

Laravel API（`/api/posts`）から投稿を取得・作成する React (Vite) フロントエンドです。

## 動かし方

前提として、Laravel（Docker）側が `http://localhost` で起動しており、`GET /api/posts` / `POST /api/posts` が使える状態になっていること。

```bash
cd my-frontend
npm install    # 初回のみ
npm run dev
```

コマンド実行後に表示される URL（通常 `http://localhost:5173`）をブラウザで開くと、投稿一覧と新規投稿フォームが表示されます。

その他のコマンド:

- `npm run build` : 本番用ビルドを `dist/` に出力
- `npm run preview` : ビルド済みの内容をローカルで確認
- `npm run lint` : oxlint による静的解析

## コンポーネント構成

- [src/App.jsx](src/App.jsx) : 投稿一覧の取得（`fetchPosts`）と全体の state を保持し、`PostForm` と `PostList` を組み合わせる親コンポーネント
- [src/PostForm.jsx](src/PostForm.jsx) : 新規投稿フォーム。入力値を state で管理し、送信時に axios で POST する
- [src/PostList.jsx](src/PostList.jsx) : 投稿一覧の表示（読み込み中・エラー・0件・一覧の出し分け）
- [src/PostItem.jsx](src/PostItem.jsx) : 投稿1件分の表示

## コンポーネント分割の理由

`PostList`（一覧の取得結果を並べる責務）、`PostItem`（1件分の投稿表示の責務）、`PostForm`（新規投稿の入力・送信の責務）の3つに分割している。それぞれ「表示する対象の単位」と「変更される理由」が異なるためで、一覧のレイアウトを変えたいときは `PostList` だけ、1件のカード内の見た目を変えたいときは `PostItem` だけ、投稿フォームの項目やバリデーションを変えたいときは `PostForm` だけを触ればよく、他のコンポーネントに影響を与えない。また `PostItem` を独立させたことで、一覧以外の場所（詳細画面など）でも同じ表示をそのまま再利用できる。
