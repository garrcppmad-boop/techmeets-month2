<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * パフォーマンス計測用の大量データ。
 *   docker compose exec app php artisan db:seed --class=PerformanceSeeder
 *
 * 1件ずつ create() すると遅いので、まとめて INSERT する。
 */
class PerformanceSeeder extends Seeder
{
    private const USERS = 100;
    private const POSTS = 20000;
    private const THREADS = 2000;
    private const REPLIES = 20000;
    private const PRODUCTS = 2000;

    public function run(): void
    {
        $now = Carbon::now();
        $password = Hash::make('password');

        $this->insert('users', self::USERS, fn(int $i) => [
            'name' => fake()->name(),
            'email' => "perf{$i}@example.com",
            'password' => $password,
            'email_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $userIds = User::pluck('id')->all();

        $this->insert('posts', self::POSTS, fn() => $this->timestamps([
            'user_id' => fake()->randomElement($userIds),
            'title' => fake()->sentence(6),
            'content' => fake()->paragraphs(5, true),
            'category' => fake()->randomElement(Post::categories()),
        ]));

        $this->insert('threads', self::THREADS, fn() => $this->timestamps([
            'user_id' => fake()->randomElement($userIds),
            'title' => fake()->sentence(6),
            'body' => fake()->paragraph(),
        ]));
        $threadIds = DB::table('threads')->pluck('id')->all();

        $this->insert('replies', self::REPLIES, fn() => $this->timestamps([
            'thread_id' => fake()->randomElement($threadIds),
            'user_id' => fake()->randomElement($userIds),
            'body' => fake()->paragraph(),
        ]));

        $this->insert('products', self::PRODUCTS, fn() => $this->timestamps([
            'name' => fake()->words(3, true),
            'price' => fake()->numberBetween(100, 50000),
            'description' => fake()->paragraph(),
            'stock' => fake()->numberBetween(0, 100),
            'category' => fake()->randomElement(Product::categories()),
        ]));
    }

    /**
     * 過去1年の中でランダムな作成日時を付ける
     */
    private function timestamps(array $row): array
    {
        $at = Carbon::now()->subMinutes(fake()->numberBetween(0, 60 * 24 * 365));

        return $row + ['created_at' => $at, 'updated_at' => $at];
    }

    private function insert(string $table, int $count, callable $make): void
    {
        $rows = [];
        for ($i = 1; $i <= $count; $i++) {
            $rows[] = $make($i);
            if (count($rows) === 1000) {
                DB::table($table)->insert($rows);
                $rows = [];
            }
        }
        if ($rows !== []) {
            DB::table($table)->insert($rows);
        }
        $this->command?->info("{$table}: {$count} 件");
    }
}
