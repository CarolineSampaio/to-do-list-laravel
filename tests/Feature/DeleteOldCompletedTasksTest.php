<?php

namespace Tests\Feature;

use App\Jobs\DeleteOldCompletedTasks;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DeleteOldCompletedTasksTest extends TestCase
{
    use RefreshDatabase;

    public function testCanDeleteTasksCompletedMoreThanAWeekAgo()
    {
        $oldTask = Task::factory()->create([
            'is_completed' => true,
            'completed_at' => Carbon::now()->subDays(8),
        ]);

        $recentTask = Task::factory()->create([
            'is_completed' => true,
            'completed_at' => Carbon::now()->subDays(3),
        ]);

        (new DeleteOldCompletedTasks())->handle();

        $this->assertDatabaseMissing('tasks', ['id' => $oldTask->id]);

        $this->assertDatabaseHas('tasks', ['id' => $recentTask->id]);
    }

    public function testCanNotDeleteTasksCompletedLessThanAWeekAgo()
    {
        $task = Task::factory()->create([
            'is_completed' => true,
            'completed_at' => Carbon::now()->subDays(6),
        ]);

        (new DeleteOldCompletedTasks())->handle();

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function testCanNotDeleteIncompleteTasks()
    {
        $incompleteTask = Task::factory()->create([
            'is_completed' => false,
            'completed_at' => null,
        ]);

        (new DeleteOldCompletedTasks())->handle();

        $this->assertDatabaseHas('tasks', ['id' => $incompleteTask->id]);
    }
}
