<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use App\Models\Category;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    public function testCategoryUserRelationship()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($category->user->id, $user->id);
    }

    public function testTaskCategoryRelationship()
    {
        $category = Category::factory()->create();
        $task = Task::factory()->create(['category_id' => $category->id]);

        $this->assertEquals($task->category->id, $category->id);
    }

    public function testUserTasksRelationship()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $user->tasks()->attach($task);

        $this->assertTrue($user->tasks->contains($task));
    }

    public function testTaskUsersRelationship()
    {
        $task = Task::factory()->create();
        $user = User::factory()->create();

        $task->users()->attach($user);

        $this->assertTrue($task->users->contains($user));
    }

    public function testTaskBelongsToUser()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['completed_by' => $user->id]);

        $this->assertEquals($user->id, $task->completed_by);
        $this->assertInstanceOf(User::class, $task->completedBy);
    }
}
