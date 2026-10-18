# セキュリティテストレポート

**対象アプリ:** techmeets-month2 (Laravel + Breeze)  
**実施日:** 2026-07-06  
**対象ブランチ:** week7/blog-system  
**テスト方法:** 静的コード解析 + 実行環境での動的テスト

---

## テスト結果サマリー

| テスト項目 | 結果 |
|---|---|
| XSS対策 | ✅ PASS |
| CSRF対策 | ✅ PASS |
| SQLインジェクション対策 | ✅ PASS |
| パスワードハッシュ化（必須） | ✅ PASS |
| 強力なアルゴリズム（bcrypt） | ✅ PASS |
| ソルト付きハッシュ | ✅ PASS |

---

## 1. XSS（クロスサイトスクリプティング）対策

### テスト内容
悪意のあるスクリプトタグを入力値として渡した場合に、HTMLとして実行されないか検証。

### 実行テスト
```
入力値:  <script>alert("XSS")</script>
出力値:  &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;
結果:    OK（タグが無効化されブラウザで実行されない）
```

### 確認ポイント
- ビュー全体（30ファイル）を検査した結果、`{!! !!}`（エスケープなし出力）は **0件**
- 全ての変数出力が `{{ }}` による自動エスケープを使用

### 判定: ✅ PASS

---

## 2. CSRF（クロスサイトリクエストフォージェリ）対策

### テスト内容
全POSTフォームに `@csrf` トークンが含まれているか検査。

### 確認結果
`@csrf` が含まれているファイル（24箇所）：

| ファイル | 件数 |
|---|---|
| auth/login, register, forgot-password 等 | 8箇所 |
| posts/create, edit, show（削除フォーム） | 3箇所 |
| threads/create, show（削除・レスフォーム） | 4箇所 |
| products/create, edit, index, show | 4箇所 |
| profile/パスワード変更・プロフィール更新 | 3箇所 |
| layouts/navigation（ログアウト） | 2箇所 |

- CSRF除外リスト（`except`）への追加: **0件**
- LaravelのCSRFミドルウェアはデフォルトで全POSTルートに適用

### 判定: ✅ PASS

---

## 3. SQLインジェクション対策

### テスト内容
悪意のあるSQL文字列を検索条件に渡した場合、クエリが安全に処理されるか検証。

### 実行テスト
```
入力値:  ' OR '1'='1
実行SQL: select * from `posts` where `title` = ?
バインド値: ["' OR '1'='1"]
結果:    OK（プレースホルダー ? でバインドされ、SQL文として解釈されない）
```

### 確認ポイント
- 危険な生クエリ（`whereRaw`, `selectRaw`, `DB::statement`, `DB::unprepared`）の使用: **0件**
- 全データベース操作が Eloquent ORM のパラメータバインディングを使用

### 判定: ✅ PASS

---

## 4. パスワードハッシュ化

### テスト内容
パスワードが平文でなくハッシュ化されて保存されるか、強力なアルゴリズムが使用されているか、
同じパスワードでも毎回異なるハッシュ値（ソルト付き）になるか検証。

### 実行テスト
```
入力パスワード:  TestPassword123!

--- ハッシュ化確認 ---
ハッシュ値: $2y$12$fdzZMVw8lGwxj2Koz4uI7OKfltwuDLftNxgU1xApOoS.j5oFmX7Tm
bcrypt使用:  YES（$2y$ はbcryptの識別子）
コスト係数:  12（2^12 = 4096回のハッシュ演算 → ブルートフォース耐性）

--- 検証テスト ---
正しいパスワードで検証: OK（一致）
誤ったパスワードで検証: OK（不一致）

--- ソルト確認 ---
同じパスワードで2回ハッシュ化 → 異なるハッシュ値: OK（ソルトが異なる）
```

### ハッシュ値の読み方
```
$2y $ 12 $ fdzZMVw8lGwxj2Koz4uI7O ... 
 ↑     ↑    ↑
アルゴリズム  コスト  ソルト＋ハッシュ（22文字のソルト含む）
```

### 実装確認（ソースコード）
```php
// RegisteredUserController.php
'password' => Hash::make($request->password),  // 登録時

// NewPasswordController.php
'password' => Hash::make($request->password),  // パスワードリセット時

// PasswordController.php
'password' => Hash::make($validated['password']),  // パスワード変更時
```
全ての箇所で `Hash::make()` を使用。平文保存: **0件**

### 判定: ✅ PASS（bcrypt / ソルト付き / 平文保存なし）

---

## 既知の改善点（前回レポートより）

| 優先度 | 問題 | 対象ファイル |
|---|---|---|
| 🟡 中 | Post の `content` に文字数制限なし | PostController 32行目 |
| 🟡 中 | `user_id` が `$fillable` に含まれている | Post モデル |
| 🟢 低 | Reply のスレッド帰属チェックなし | ReplyController 25行目 |
| 🔵 情報 | 本番環境でのHTTPS強制設定が必要 | .env |
