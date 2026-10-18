<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Post;
use App\Models\Task;
use App\Policies\PostPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);

        // N+1 問題の検出: 一覧で取得したモデルのリレーションを eager loading せずに
        // 参照したら例外にする（本番では無効。開発・テストで気付けるようにする）
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
