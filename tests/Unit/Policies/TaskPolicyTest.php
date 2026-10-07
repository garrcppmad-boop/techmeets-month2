<?php

namespace Tests\Unit\Policies;

use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    private TaskPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TaskPolicy();
    }

    public function test_owner_can_view(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->view($user, $task));
    }

    public function test_non_owner_cannot_view(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->view($other, $task));
    }

    public function test_owner_can_update(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->update($user, $task));
    }

    public function test_non_owner_cannot_update(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->update($other, $task));
    }

    public function test_owner_can_delete(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->delete($user, $task));
    }

    public function test_non_owner_cannot_delete(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->delete($other, $task));
    }
}
