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

    // list tasks
    public function testUserCanFilterTasksByCategory()
    {
        $user =  User::factory()->create();
        Sanctum::actingAs($user);

        $category1 = Category::factory()->create(['user_id' => $user->id]);
        $category2 = Category::factory()->create(['user_id' => $user->id]);

        $tasks = Task::factory(3)->create(['category_id' => $category1->id]);
        $tasks->each(function ($task) use ($user) {
            $task->users()->attach($user->id);
        });

        Task::factory(2)->create(['category_id' => $category2->id]);

        $response = $this->getJson('/api/tasks?category=' . $category1->id);

        $response->assertStatus(200)->assertJsonCount(3, 'data.*');
    }

    public function testUserCanFilterTasksByCompleted()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id]);
        $tasks = Task::factory(3)->create(['category_id' => $category->id]);
        $tasks->each(function ($task) use ($user) {
            $task->users()->attach($user->id);
        });

        $completedTasks = Task::factory(2)->create(['category_id' => $category->id]);
        $completedTasks->each(function ($task) use ($user) {
            $task->users()->attach($user->id);
            $this->patchJson("/api/tasks/{$task->id}/complete");
        });

        $response = $this->getJson('/api/tasks?completed=true');

        $response->assertStatus(200)->assertJsonCount(2, 'data.*');
    }

    public function testUserCanFilterTasksByCategoryAndCompleted()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category1 = Category::factory()->create(['user_id' => $user->id]);

        $tasks = Task::factory(3)->create(['category_id' => $category1->id]);
        $tasks->each(function ($task) use ($user) {
            $task->users()->attach($user->id);
        });

        $completedTasks = Task::factory(2)->create(['category_id' => $category1->id]);
        $completedTasks->each(function ($task) use ($user) {
            $task->users()->attach($user->id);
            $this->patchJson("/api/tasks/{$task->id}/complete");
        });

        $response = $this->getJson('/api/tasks?category=' . $category1->id . '&completed=true');
        $response->assertStatus(200)->assertJsonCount(2, 'data.*');
    }

    // update task
    public function testUserCanUpdateHisOwnTask()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create(['category_id' => $category->id]);
        $task->users()->attach($user->id);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Task',
            'description' => 'This is an updated task',
            'category_id' => $category->id,
        ]);

        $response->assertStatus(200)->assertJsonFragment([
            'title' => 'Updated Task',
            'description' => 'This is an updated task',
            'category_id' => $category->id,
        ]);
    }

    public function testUserCanUpdateHisOwnTaskWithoutCategory()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $task = Task::factory()->create();
        $task->users()->attach($user->id);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Task',
            'description' => 'This is an updated task',
        ]);

        $response->assertStatus(200)->assertJsonFragment([
            'title' => 'Updated Task',
            'description' => 'This is an updated task',
            'category_id' => null,
        ]);
    }

    public function testUserCanUpdateHisOwnTaskWithoutDescription()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $task = Task::factory()->create();
        $task->users()->attach($user->id);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Task',
        ]);

        $response->assertStatus(200)->assertJsonFragment([
            'title' => 'Updated Task',
            'description' => null,
        ]);
    }

    public function testUserCanUpdateOnlyDescription()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create(['category_id' => $category->id]);
        $task->users()->attach($user->id);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'description' => 'This is an updated task',
        ]);

        $response->assertStatus(200)->assertJsonFragment([
            'description' => 'This is an updated task',
        ]);
    }

    public function testUserCanUpdateOnlyCategory()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create();
        $task->users()->attach($user->id);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'category_id' => $category->id,
        ]);

        $response->assertStatus(200)->assertJsonFragment([
            'category_id' => $category->id,
        ]);
    }

    public function testUserCanNotUpdateTaskWithEmptyTitle()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $task = Task::factory()->create();
        $task->users()->attach($user->id);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => '',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('title');
    }

    public function testUserCanNotUpdateTaskWithInvalidCategory()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $task = Task::factory()->create();
        $task->users()->attach($user->id);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Task',
            'category_id' => 999,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('category_id');
    }

    public function testUserCanNotUpdateTaskWithInvalidCategoryType()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $task = Task::factory()->create();
        $task->users()->attach($user->id);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Task',
            'category_id' => 'invalid',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('category_id');
    }

    public function testUserCanNotUpdateTaskWithCategoryThatBelongsToAnotherUser()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $task = Task::factory()->create();
        $task->users()->attach($user->id);

        $user2 = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user2->id]);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Task',
            'category_id' => $category->id,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('category_id');
    }

    public function testUserCanNotUpdateTaskThatBelongsToAnotherUser()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $task = Task::factory()->create();
        $user2 = User::factory()->create();
        $task->users()->attach($user2->id);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Task',
        ]);

        $response->assertStatus(404)->assertJsonFragment([
            'message' => 'Tarefa não encontrada',
        ]);
    }

    public function testUserCanNotUpdateTaskWithoutAuthentication()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        $task->users()->attach($user->id);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Task',
        ]);

        $response->assertStatus(401);
    }

    public function testUserCanNotUpdateTaskWithInvalidToken()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        $task->users()->attach($user->id);

        $response = $this->withHeader('Authorization', 'Bearer invalid_token')->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Task',
        ]);

        $response->assertStatus(401);
    }

    // delete task
    public function testUserCanDeleteHisOwnTask()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $task = Task::factory()->create();
        $task->users()->attach($user->id);

        $response = $this->delete("/api/tasks/{$task->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function testUserCanNotDeleteTaskThatBelongsToAnotherUser()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $user2 = User::factory()->create();
        $task = Task::factory()->create();
        $task->users()->attach($user2->id);

        $response = $this->delete("/api/tasks/{$task->id}");

        $response->assertStatus(404)->assertJsonFragment([
            'message' => 'Tarefa não encontrada',
        ]);
    }

    public function testUserCanNotDeleteTaskWithoutAuthentication()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        $task->users()->attach($user->id);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(401)->assertJsonFragment([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testUserCanNotDeleteTaskWithInvalidToken()
    {
        $task = Task::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer 123')->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(401)->assertJsonFragment([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testUserCanNotDeleteNonExistingTask()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->delete("/api/tasks/999");

        $response->assertStatus(404)->assertJsonFragment([
            'message' => 'Tarefa não encontrada',
        ]);
    }

    public function testUserCanNotDeleteTaskWithInvalidId()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->delete("/api/tasks/invalid");

        $response->assertStatus(404)->assertJsonFragment([
            'message' => 'Tarefa não encontrada',
        ]);
    }

    public function testUserCanNotDeleteTaskWithNonNumericId()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->delete("/api/tasks/invalid");

        $response->assertStatus(404)->assertJsonFragment([
            'message' => 'Tarefa não encontrada',
        ]);
    }

    // complete task
    public function testUserCanCompleteTask()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create([
            'category_id' => $category->id,
        ]);
        $task->users()->attach($user->id);

        $response = $this->patchJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tarefa atualizada com sucesso',
                'status' => 200,
                'data' => [
                    'id' => $task->id,
                    'is_completed' => true,
                    'completed_by' => [
                        'id' => $user->id,
                        'name' => $user->name,
                    ],
                    'completed_at' => $response->json('data.completed_at')
                ]
            ]);
    }

    public function testUserCanUnCompleteTask()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create([
            'category_id' => $category->id,
            'is_completed' => true,
            'completed_by' => $user->id,
            'completed_at' => now(),
        ]);

        $task->users()->attach($user->id);

        $response = $this->patchJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tarefa atualizada com sucesso',
                'data' => [
                    'is_completed' => false,
                    'completed_by' => null,
                    'completed_at' => null,
                ]
            ]);
    }

    public function testUserCanNotCompleteTaskWithInvalidId()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/tasks/999/complete");

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Tarefa não encontrada',
            ]);
    }

    public function testUserCanNotCompleteTaskWithNonNumericId()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/tasks/invalid/complete");

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Tarefa não encontrada',
            ]);
    }

    public function testUserCanNotCompleteTaskThatBelongsToAnotherUser()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($anotherUser);

        $category = Category::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create([
            'category_id' => $category->id,
            'is_completed' => false,
        ]);

        $task->users()->attach($user->id);

        $response = $this->patchJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Tarefa não encontrada',
            ]);
    }

    public function testUserCanNotCompleteTaskWithoutAuthentication()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        $task->users()->attach($user->id);

        $response = $this->patchJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function testUserCanNotCompleteTaskWithInvalidToken()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        $task->users()->attach($user->id);

        $response = $this->withHeader('Authorization', 'Bearer invalid_token')->patchJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
