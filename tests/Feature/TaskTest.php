<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskTest extends TestCase
{
    // create a task
    public function testUserCanCreateTask()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'description' => 'This is a test task',
            'category_id' => $category->id
        ]);

        $response->assertStatus(201)->assertJsonFragment([
            'title' => 'Test Task',
            'description' => 'This is a test task',
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('task_user', [
            'task_id' => $response->json('data.id'),
            'user_id' => $user->id,
        ]);
    }

    public function testUserCanCreateTaskWithoutCategory()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'description' => 'This is a test task',
        ]);

        $response->assertStatus(201)->assertJsonFragment([
            'title' => 'Test Task',
            'description' => 'This is a test task',
            'category_id' => null,
        ]);

        $this->assertDatabaseHas('task_user', [
            'task_id' => $response->json('data.id'),
            'user_id' => $user->id,
        ]);
    }

    public function testUserCanCreateTaskWithoutDescription()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
        ]);

        $response->assertStatus(201)->assertJsonFragment([
            'title' => 'Test Task',
            'description' => null,
            'category_id' => null,
        ]);

        $this->assertDatabaseHas('task_user', [
            'task_id' => $response->json('data.id'),
            'user_id' => $user->id,
        ]);
    }

    public function testUserCanNotCreateTaskWithoutTitle()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', []);

        $response->assertStatus(422)->assertJsonValidationErrors('title');
    }

    public function testUserCanNotCreateTaskWithEmptyTitle()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => '',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('title');
    }

    public function testUserCanNotCreateTaskWithInvalidCategory()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'category_id' => 999,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('category_id');
    }

    public function testUserCanNotCreateTaskWithInvalidCategoryType()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'category_id' => 'invalid',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('category_id');
    }

    public function testUserCanNotCreateTaskWithCategoryThatBelongsToAnotherUser()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $user2 = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user2->id]);


        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'category_id' => $category->id,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('category_id');
    }

    public function testUserCanNotCreateTaskWithoutAuthentication()
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
        ]);

        $response->assertStatus(401);
    }
}
