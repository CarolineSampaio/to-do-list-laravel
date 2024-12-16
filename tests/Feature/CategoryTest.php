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
}
