<?php

namespace Tests\Unit\Models;

use App\Models\Task;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_statuses_returns_fixed_map(): void
    {
        $this->assertSame([
            'pending' => '未着手',
            'in_progress' => '進行中',
            'done' => '完了',
        ], Task::statuses());
    }

    public function test_due_date_is_cast_to_date(): void
    {
        $task = Task::factory()->create(['due_date' => '2026-12-31']);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $task->due_date);
        $this->assertSame('2026-12-31', $task->due_date->format('Y-m-d'));
    }
}
