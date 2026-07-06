# セキュリティテストレポート

**対象アプリ:** techmeets-month2 (Laravel + Breeze)  
**実施日:** 2026-07-06  
**対象ブランチ:** week7/blog-system  

---

## 総合評価

| カテゴリ | 評価 |
|---|---|
| 認証・認可 | ✅ 良好 |
| XSS対策 | ✅ 良好 |
| CSRF対策 | ✅ 良好 |
| SQLインジェクション | ✅ 良好 |
| 入力バリデーション | ⚠️ 一部不足 |
| マスアサインメント | ⚠️ 要改善 |
| その他 | ⚠️ 軽微な問題あり |

---

## 問題一覧

### 🟡 中 : Post の content にmax制約なし

**ファイル:** `app/Http/Controllers/PostController.php` 32行目  
**内容:**  
```php
'content' => 'required',  // max制約がない
```
`content` フィールドに文字数制限がないため、極端に大きなデータを送信されると  
メモリ不足やレスポンス遅延を引き起こす可能性がある。

**修正:**
```php
'content' => 'required|max:10000',
```

---

### 🟡 中 : user_id が Post モデルの $fillable に含まれている

**ファイル:** `app/Models/Post.php` 9行目  
**内容:**
```php
protected $fillable = [
    'user_id',  // ← 本来サーバー側で設定すべき値
    'title',
    'content',
    'category',
];
```
`user_id` を `$fillable` に入れると、将来的に別のメソッドで  
`Post::create($request->all())` のような書き方をした際に  
ユーザーIDを書き換えられるリスクがある。

**修正:** `user_id` を `$fillable` から除外し、直接代入する
```php
protected $fillable = ['title', 'content', 'category'];

// controller側
$post = Post::create($validated);
$post->user_id = auth()->id();
$post->save();
```

---

### 🟢 低 : ReplyController でスレッド帰属チェックなし

**ファイル:** `app/Http/Controllers/ReplyController.php` 25行目  
**内容:**  
```php
public function destroy(Thread $thread, Reply $reply)
{
    if (! $reply->isOwnedBy(auth()->id())) {
        abort(403);
    }
    // $reply が $thread に属するかのチェックがない
```
`DELETE /threads/99/replies/5` のように、別スレッドのURLを経由して  
自分のレスを削除できてしまう（実害は限定的だが論理的に不正）。

**修正:**
```php
if ($reply->thread_id !== $thread->id) {
    abort(404);
}
```

---

### 🔵 情報 : HTTPS の強制設定なし

**ファイル:** `.env`  
**内容:**  
本番環境では `APP_URL` が `https://` で始まるべきだが、現在は `http://localhost`。  
セッションクッキーをHTTPS限定にする設定も未適用。

**修正（本番デプロイ時）:**
```
APP_URL=https://your-domain.com
SESSION_SECURE_COOKIE=true
FORCE_HTTPS=true
```

---

### 🔵 情報 : ProductController がルートに未登録

**ファイル:** `routes/web.php`  
**内容:**  
`ProductController` は存在するが `web.php` に対応ルートがない。  
`/products` にアクセスすると 404 になる。  
不要なコントローラーは削除するか、ルートを追加すること。

---

## 問題なし（対策済み）の項目

| 項目 | 実装箇所 | 評価 |
|---|---|---|
| **XSS対策** | Bladeの `{{ }}` が自動エスケープ | ✅ |
| **CSRF対策** | 全フォームに `@csrf` | ✅ |
| **SQLインジェクション** | Eloquent の パラメータバインディング | ✅ |
| **認証ガード** | `Route::middleware('auth')` で保護 | ✅ |
| **認可（所有権）** | `isOwnedBy()` で自分のデータのみ操作可能 | ✅ |
| **マスアサインメント** | モデルに `$fillable` を明示 | ✅ |
| **ブルートフォース対策** | Breeze の RateLimiter（5回/分） | ✅ |
| **パスワードハッシュ** | Breeze が `bcrypt` で自動ハッシュ | ✅ |
| **バリデーション** | 全ミューテーション操作で `validate()` | ✅ |

---

## 修正優先度

| 優先度 | 問題 |
|---|---|
| 高 | `content` フィールドに `max` 制約を追加 |
| 中 | `user_id` を `$fillable` から除外 |
| 低 | Reply のスレッド帰属チェックを追加 |
| 本番時 | HTTPS強制・セキュアクッキーを設定 |
