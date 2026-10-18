# リファクタリング演習: Fat Controller → Repository/Service パターン

## 題材

タスク管理アプリの `TaskController` を例に、  
**Fat Controller（肥大化したコントローラー）** を段階的にリファクタリングする。

---

## BEFORE: Fat Controller（リファクタリング前）

> **問題点**: コントローラー1クラスに「DB操作・認可・ビジネスロジック・ログ・トランザクション」が全部詰まっている。

```php
// app/Http/Controllers/TaskController.php（リファクタリング前）

class TaskController extends Controller
{
    // ❌ 問題1: index() が直接 Eloquent を操作している
    //    → 並び順やページ数を変えたいとき、必ずコントローラーを触らないといけない
    public function index()
    {
        $tasks = Task::where('user_id', auth()->id())           // DB操作がここに直書き
            ->orderByRaw("FIELD(status, 'in_progress', 'pending', 'done')")
            ->orderBy('due_date')
            ->paginate(15);

        return view('tasks.index', compact('tasks'));
    }

    // ❌ 問題2: show() が認可ロジックを自前で実装している
    //    → 「所有者チェック」のルールが複数の場所に散在しやすい
    public function show(int $id)
    {
        $task = Task::findOrFail($id);

        if ($task->user_id !== auth()->id()) {   // 認可ロジックがここに直書き
            abort(403, 'このタスクは表示できません');
        }

        return view('tasks.show', compact('task'));
    }

    // ❌ 問題3: store() に「バリデーション・認可・DB操作・トランザクション・ログ」が全部ある
    //    → メソッドが長くなり、何をしているか一目で分からない
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|max:200',
            'description' => 'nullable|max:5000',
            'status'      => 'required|in:pending,in_progress,done',
            'due_date'    => 'nullable|date|after_or_equal:today',
        ]);

        // トランザクションとログもここに直書き
        DB::beginTransaction();
        try {
            $task = new Task();
            $task->user_id     = auth()->id();   // user_id のセットも手動
            $task->title       = $validated['title'];
            $task->description = $validated['description'] ?? null;
            $task->status      = $validated['status'];
            $task->due_date    = $validated['due_date'] ?? null;
            $task->save();

            Log::info('Task created', ['task_id' => $task->id]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Task creation failed', ['error' => $e->getMessage()]);
            throw $e;
        }

        return redirect()->route('tasks.show', $task)->with('success', 'タスクを作成しました');
    }

    // ❌ 問題4: edit() と update() で認可ロジックが重複している
    public function edit(int $id)
    {
        $task = Task::findOrFail($id);

        if ($task->user_id !== auth()->id()) {   // ← show() と全く同じコードの重複
            abort(403);
        }

        return view('tasks.edit', compact('task'));
    }

    public function update(Request $request, int $id)
    {
        $task = Task::findOrFail($id);

        if ($task->user_id !== auth()->id()) {   // ← また同じ重複
            abort(403);
        }

        $validated = $request->validate([
            'title'       => 'required|max:200',
            'description' => 'nullable|max:5000',
            'status'      => 'required|in:pending,in_progress,done',
            'due_date'    => 'nullable|date',
        ]);

        DB::beginTransaction();                  // トランザクションも重複
        try {
            $task->update($validated);
            Log::info('Task updated', ['task_id' => $task->id]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()->route('tasks.show', $task)->with('success', 'タスクを更新しました');
    }

    // ❌ 問題5: destroy() も同様に認可・DB操作・ログを直書き
    public function destroy(int $id)
    {
        $task = Task::findOrFail($id);

        if ($task->user_id !== auth()->id()) {   // ← 4回目の同じ重複
            abort(403);
        }

        DB::beginTransaction();
        try {
            $task->delete();
            Log::info('Task deleted', ['task_id' => $task->id]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()->route('tasks.index')->with('success', 'タスクを削除しました');
    }
}
```

### Fat Controller の問題まとめ

| 問題 | 具体例 | 影響 |
|---|---|---|
| DB操作の直書き | `Task::where(...)->paginate()` がコントローラー内 | 並び順変更のたびにコントローラーを修正 |
| 認可ロジックの重複 | `if ($task->user_id !== auth()->id()) abort(403)` が4箇所 | 1か所修正漏れでセキュリティホールになる |
| トランザクションの重複 | `DB::beginTransaction/commit/rollBack` が3箇所 | 書き忘れ・書き間違いが起きやすい |
| ログの重複 | `Log::info(...)` が各メソッドに散在 | ログのフォーマット変更で全メソッド修正が必要 |
| ルートモデルバインディング未使用 | `int $id` を受け取り手動で `findOrFail` | 冗長、Laravelの機能を活かせていない |

---

## リファクタリングの手順

### Step 1: 認可ロジックを Policy に集約

**変更点**: `if (user_id !== auth()->id()) abort(403)` → `TaskPolicy` クラスに切り出す

```php
// app/Policies/TaskPolicy.php（新規作成）

class TaskPolicy
{
    // 「誰が何をできるか」のルールをここだけに書く
    public function view(User $user, Task $task): bool   { return $user->id === $task->user_id; }
    public function update(User $user, Task $task): bool { return $user->id === $task->user_id; }
    public function delete(User $user, Task $task): bool { return $user->id === $task->user_id; }
}
```

```php
// コントローラー側は1行になる
$this->authorize('update', $task);  // ← PolicyのupdateメソッドをLaravelが自動で呼ぶ
```

**効果**: 認可ルールの変更は `TaskPolicy` の1クラスだけ修正すれば全メソッドに反映される。

---

### Step 2: DB操作を Repository に切り出す

**変更点**: Eloquent の呼び出しをコントローラーから分離する

```php
// app/Repositories/TaskRepository.php（新規作成）

class TaskRepository
{
    public function getAllForUser(int $userId): LengthAwarePaginator
    {
        return Task::where('user_id', $userId)
            ->orderByRaw("FIELD(status, 'in_progress', 'pending', 'done')")
            ->orderBy('due_date')
            ->paginate(15);
    }

    public function create(int $userId, array $data): Task
    {
        $task = new Task($data);
        $task->user_id = $userId;  // $fillable から user_id を外してここで直接セット
        $task->save();
        return $task;
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
```

**効果**: 並び順・ページ数・クエリの変更は `TaskRepository` だけを修正すればよい。

---

### Step 3: ビジネスロジックを Service に切り出す

**変更点**: トランザクション・ログをコントローラーから分離する

```php
// app/Services/TaskService.php（新規作成）

class TaskService
{
    public function __construct(private TaskRepository $taskRepository) {}

    public function createTask(int $userId, array $data): Task
    {
        // DB::transaction() はクロージャ内が全部成功 → commit、例外 → 自動 rollBack
        return DB::transaction(function () use ($userId, $data) {
            $task = $this->taskRepository->create($userId, $data);
            Log::info('Task created', ['task_id' => $task->id, 'user_id' => $userId]);
            return $task;
        });
    }

    // update・delete も同様のパターン
}
```

**効果**: `DB::beginTransaction/commit/rollBack` の書き忘れがなくなる。  
ログのフォーマット変更は `TaskService` だけを修正すればよい。

---

## AFTER: Thin Controller（リファクタリング後）

```php
// app/Http/Controllers/TaskController.php（リファクタリング後）

class TaskController extends Controller
{
    // ✅ コンストラクタインジェクション: 使うものを宣言するだけ
    public function __construct(
        private TaskService    $taskService,
        private TaskRepository $taskRepository
    ) {}

    // ✅ index(): 「データを取ってビューに渡す」だけ
    public function index()
    {
        $tasks = $this->taskRepository->getAllForUser(auth()->id());
        return view('tasks.index', compact('tasks'));
    }

    // ✅ show(): 「認可チェック → ビュー表示」だけ
    public function show(Task $task)
    {
        $this->authorize('view', $task);  // Policyに委譲
        return view('tasks.show', compact('task'));
    }

    // ✅ store(): 「バリデーション → Serviceに委譲 → リダイレクト」だけ
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|max:200',
            'description' => 'nullable|max:5000',
            'status'      => 'required|in:' . implode(',', array_keys(Task::statuses())),
            'due_date'    => 'nullable|date|after_or_equal:today',
        ]);

        $task = $this->taskService->createTask(auth()->id(), $validated);

        return redirect()->route('tasks.show', $task)->with('success', 'タスクを作成しました');
    }

    // ✅ update(): 「認可 → バリデーション → Serviceに委譲 → リダイレクト」だけ
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([/* 同上 */]);

        $task = $this->taskService->updateTask($task, $validated);

        return redirect()->route('tasks.show', $task)->with('success', 'タスクを更新しました');
    }

    // ✅ destroy(): 「認可 → Serviceに委譲 → リダイレクト」だけ（3行！）
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $this->taskService->deleteTask($task);
        return redirect()->route('tasks.index')->with('success', 'タスクを削除しました');
    }
}
```

---

## Before / After 比較

### コード行数

| クラス | Before | After |
|---|---|---|
| TaskController | 85行（1ファイル） | 55行 |
| TaskRepository | — | 35行（新規） |
| TaskService | — | 35行（新規） |
| TaskPolicy | — | 18行（新規） |
| **合計** | **85行** | **143行** |

> 合計行数は増える。それでもリファクタリングする理由 → 「変更しやすさ」と「責任の明確さ」が向上するから。

---

### 責任の分離（単一責任原則）

```
BEFORE: TaskController が全部やる
┌─────────────────────────────────────────────────┐
│ TaskController                                  │
│  ├── DB操作 (Task::where, Task::findOrFail)     │
│  ├── 認可チェック (if user_id !== auth()->id()) │
│  ├── トランザクション (DB::beginTransaction)    │
│  ├── ログ (Log::info)                           │
│  └── HTTPレスポンス (view, redirect)            │
└─────────────────────────────────────────────────┘

AFTER: 1クラス1責任
┌──────────────────────┐   ┌──────────────────────┐
│ TaskController       │   │ TaskPolicy           │
│  - HTTPの入出力のみ  │   │  - 認可ルールのみ    │
└──────┬───────────────┘   └──────────────────────┘
       │ 委譲
       ▼
┌──────────────────────┐
│ TaskService          │
│  - トランザクション  │
│  - ログ記録          │
│  - ビジネスロジック  │
└──────┬───────────────┘
       │ 委譲
       ▼
┌──────────────────────┐
│ TaskRepository       │
│  - DB操作のみ        │
│  - クエリのみ        │
└──────────────────────┘
```

---

### 変更への強さ（修正が必要なファイル数）

| やりたいこと | Before（修正ファイル） | After（修正ファイル） |
|---|---|---|
| タスクの並び順を変える | TaskController | **TaskRepository のみ** |
| 認可ロジックを変える（管理者は全タスク操作可） | TaskController の show/edit/update/destroy 全部 | **TaskPolicy のみ** |
| ログのフォーマットを変える | TaskController の store/update/destroy 全部 | **TaskService のみ** |
| バリデーションを変える | TaskController | TaskController（HTTPの責任なので正しい） |

---

### テストしやすさ

```
BEFORE: コントローラーのテストには HTTP リクエストが必要
→ ミドルウェア・セッション・DBが全部絡み合う → テストが重い

AFTER: 各層を独立してテストできる
TaskRepository → DBだけ用意すれば良い
TaskService    → Repository をモックして純粋なロジックだけテスト
TaskPolicy     → User/Task インスタンスを渡すだけでテスト可能
TaskController → Service/Repository をモックして HTTP レベルだけテスト
```

---

## リファクタリングの進め方（実践的な順序）

```
1. まず動くコードを書く（Fat Controller でOK）
         ↓
2. 認可ロジックが重複してきたら → Policy に切り出す
         ↓
3. Eloquent 呼び出しがコントローラーに増えてきたら → Repository に切り出す
         ↓
4. メソッドが長くなってきたら（20行超えが目安）→ Service に切り出す
         ↓
5. コントローラーが「受け取る・委譲・返す」だけになれば完成
```

> 最初から完璧な設計を目指す必要はない。  
> 「変更したくなった瞬間」がリファクタリングのタイミング。
