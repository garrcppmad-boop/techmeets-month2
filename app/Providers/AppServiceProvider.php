<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\Task;
use App\Policies\PostPolicy;
use App\Policies\TaskPolicy;
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
    }
}
