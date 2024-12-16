<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    // create tests
    public function testUserCanCreateCategory()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = ['title' => 'New Category'];
        $response = $this->postJson('/api/categories', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data' => ['id', 'title']])
            ->assertJsonFragment(['title' => 'new category']);
    }

    public function testUserCanNotCreateCategoryWithoutAuthenticationToken()
    {
        $data = ['title' => 'New Category'];

        $response = $this->postJson('/api/categories', $data);

        $response->assertStatus(401)->assertJsonFragment([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testUserCanNotCreateCategoryWithoutTitle()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = [];
        $response = $this->postJson('/api/categories', $data);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['title']])
            ->assertJsonFragment([
                'message' => 'O título é obrigatório',
            ]);
    }

    public function testUserCanNotCreateCategoryWithEmptyTitle()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = ['title' => ''];
        $response = $this->postJson('/api/categories', $data);

        $response->assertStatus(422)->assertJsonStructure(['message', 'errors' => ['title']])->assertJsonFragment([
            'message' => 'O título é obrigatório',
        ]);
    }

    public function testUserCanNotCreateCategoryWithInvalidStringTitle()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = ['title' => 123];
        $response = $this->postJson('/api/categories', $data);

        $response->assertStatus(422)->assertJsonStructure(['message', 'errors' => ['title']])->assertJsonFragment([
            'message' => 'O título deve ser válido',
        ]);
    }

    public function testUserCanNotCreateCategoryWithTitleMoreThan150Characters()
    {
        $user = User::factory()->create();
        $token = Sanctum::actingAs($user);

        $data = ['title' => str_repeat('a', 151)];
        $response = $this->postJson('/api/categories', $data, [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(422)->assertJsonStructure(['message', 'errors' => ['title']])->assertJsonFragment([
            'message' => 'O título deve ter no máximo 150 caracteres',
        ]);
    }

    // list all tests
    public function testUserCanListCategories()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $categories = Category::factory(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.*')
            ->assertJsonStructure(
                ['message', 'status', 'data']
            );
    }

    public function testUserCanNotListCategoriesWithoutAuthenticationToken()
    {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(401)->assertJsonFragment([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testUserCanNotListCategoriesWithInvalidToken()
    {
        $response = $this->withHeader('Authorization', 'Bearer 123')->getJson('/api/categories');

        $response->assertStatus(401)->assertJsonFragment([
            'message' => 'Unauthenticated.',
        ]);
    }

    // show tests
    public function testUserCanShowCategory()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/categories/' . $category->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => $category->title])
            ->assertJsonStructure(['message', 'status', 'data' => ['*' => ['id', 'title']]]);
    }

    public function testUserCanNotShowCategoryWithoutAuthenticationToken()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/categories/' . $category->id);

        $response->assertStatus(401)->assertJsonFragment([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testUserCanNotShowCategoryWithInvalidToken()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer 123')->getJson('/api/categories/' . $category->id);

        $response->assertStatus(401)->assertJsonFragment([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testUserCanNotShowCategoryWithInvalidId()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/categories/123');

        $response->assertStatus(404)->assertJsonFragment([
            'message' => 'Categoria não encontrada',
        ]);
    }

    public function testUserCanNotShowCategoryBelongsToAnotherUser()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create(['user_id' => $anotherUser->id]);

        $response = $this->getJson('/api/categories/' . $category->id);

        $response->assertStatus(404)->assertJsonFragment([
            'message' => 'Categoria não encontrada',
        ]);
    }

    // update tests
    public function testUserCanUpdateCategory()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id]);

        $data = ['title' => 'Updated Category'];
        $response = $this->putJson('/api/categories/' . $category->id, $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data' => ['id', 'title']])
            ->assertJsonFragment(['title' => 'updated category']);
    }

    public function testUserCanNotUpdateCategoryWithoutAuthenticationToken()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $data = ['title' => 'Updated Category'];
        $response = $this->putJson('/api/categories/' . $category->id, $data);

        $response->assertStatus(401)->assertJsonFragment([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testUserCanNotUpdateCategoryWithInvalidToken()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $data = ['title' => 'Updated Category'];
        $response = $this->withHeader('Authorization', 'Bearer 123')->putJson('/api/categories/' . $category->id, $data);

        $response->assertStatus(401)->assertJsonFragment([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testUserCanNotUpdateCategoryWithoutTitle()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id]);

        $data = [];
        $response = $this->putJson('/api/categories/' . $category->id, $data);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['title']])
            ->assertJsonFragment([
                'message' => 'O título é obrigatório',
            ]);
    }

    public function testUserCanNotUpdateCategoryWithEmptyTitle()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id]);

        $data = ['title' => ''];
        $response = $this->putJson('/api/categories/' . $category->id, $data);

        $response->assertStatus(422)->assertJsonStructure(['message', 'errors' => ['title']])->assertJsonFragment([
            'message' => 'O título é obrigatório',
        ]);
    }

    public function testUserCanNotUpdateCategoryWithInvalidStringTitle()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id]);

        $data = ['title' => 123];
        $response = $this->putJson('/api/categories/' . $category->id, $data);

        $response->assertStatus(422)->assertJsonStructure(['message', 'errors' => ['title']])->assertJsonFragment([
            'message' => 'O título deve ser válido',
        ]);
    }

    public function testUserCanNotUpdateCategoryWithTitleMoreThan150Characters()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id]);

        $data = ['title' => str_repeat('a', 151)];
        $response = $this->putJson('/api/categories/' . $category->id, $data);

        $response->assertStatus(422)->assertJsonStructure(['message', 'errors' => ['title']])->assertJsonFragment([
            'message' => 'O título deve ter no máximo 150 caracteres',
        ]);
    }

    public function testUserCanNotUpdateCategoryWithInvalidId()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = ['title' => 'Updated Category'];
        $response = $this->putJson('/api/categories/123', $data);

        $response->assertStatus(404)->assertJsonFragment([
            'message' => 'Categoria não encontrada',
        ]);
    }

    public function testUserCanNotUpdateCategoryBelongsToAnotherUser()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $anotherUser = User::factory()->create();
        Sanctum::actingAs($anotherUser);

        $data = ['title' => 'Updated Category'];
        $response = $this->putJson('/api/categories/' . $category->id, $data);

        $response->assertStatus(404)->assertJsonFragment([
            'message' => 'Categoria não encontrada',
        ]);
    }
}
