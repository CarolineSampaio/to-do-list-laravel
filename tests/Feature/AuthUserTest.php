<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthUserTest extends TestCase
{
    // create user tests
    public function testCanCreateUser()
    {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
            'email' => 'test@example',
            'password' => 'passworD2@'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function testCanNotCreateUserWithoutName()
    {
        $response = $this->postJson('/api/users', [
            'email' => 'test@example',
            'password' => 'passworD2@'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name'
                ]
            ]);
    }

    public function testCanNotCreateUserWithoutEmail()
    {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
            'password' => 'passworD2@'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email'
                ]
            ]);
    }

    public function testCanNotCreateUserWithoutPassword()
    {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
            'email' => 'test@example'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'password'
                ]
            ]);
    }

    public function testCanNotCreateUserWithInvalidName()
    {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe 123',
            'email' => 'test@example',
            'password' => 'passworD2@'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name'
                ]
            ]);
    }

    public function testCanNotCreateUserWithInvalidEmail()
    {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
            'email' => 'testexample',
            'password' => 'passworD2@'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email'
                ]
            ]);
    }

    public function testCanNotCreateUserWithInvalidPassword()
    {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
            'email' => 'test@example',
            'password' => 'password'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'password'
                ]
            ]);
    }

    public function testCanNotCreateUserWithExistingEmail()
    {
        $this->postJson('/api/users', [
            'name' => 'John Doe',
            'email' => 'test@example',
            'password' => 'passworD2@'
        ]);

        $response = $this->postJson('/api/users', [
            'name' => 'Jane Doe',
            'email' => 'test@example',
            'password' => 'passworD2@'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email'
                ]
            ]);
    }

    // login tests
    public function testUserCanLogin()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'teste@example.com',
            'password' => 'Password123@',
        ]);

        $response->assertStatus(200)->assertJsonStructure([
            'message',
            'status',
            'data' => [
                'token',
            ],
        ]);
    }

    public function testUserCanNotLoginWithInvalidCredentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password1@',
        ]);

        $response->assertStatus(401)
            ->assertJsonStructure([
                'message',
                'status',
                'errors',
            ])
            ->assertJsonFragment([
                'errors' => [
                    'error' => 'Credenciais inválidas.',
                ],
            ]);
    }

    public function testUserCanNotLoginWithoutEmail()
    {
        $response = $this->postJson('/api/login', [
            'password' => 'Password123@',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email',
                ],
            ])
            ->assertJsonFragment([
                'errors' => [
                    'email' => [
                        'O email é obrigatório',
                    ],
                ],
            ]);
    }

    public function testUserCanNotLoginWithoutPassword()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(422)->assertJsonStructure([
            'message',
            'errors' => [
                'password',
            ],
        ]);

        $response->assertJsonFragment([
            'errors' => [
                'password' => [
                    'A senha é obrigatória',
                ],
            ],
        ]);
    }

    public function testUserCanNotLoginWithInvalidEmail()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => 'Password123@',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email',
                ],
            ])
            ->assertJsonFragment([
                'errors' => [
                    'email' => ['O email deve ser válido'],
                ],
            ]);
    }

    public function testUserCanNotLoginWithInvalidPassword()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example',
            'password' => 'Password',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'password',
                ],
            ])
            ->assertJsonFragment([
                'errors' => [
                    'password' => ['A senha deve conter pelo menos uma letra maiúscula, um número e um caractere especial'],
                ],
            ]);
    }

    // logout tests
    public function testUserCanLogout()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(204);
    }

    public function testUserCanNotLogoutWithoutAuthenticationToken()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401)->assertJsonFragment([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testUserCanNotLogoutWithInvalidAuthenticationToken()
    {
        $response = $this->withHeader('Authorization', 'Bearer 123')->postJson('/api/logout');

        $response->assertStatus(401)->assertJsonFragment([
            'message' => 'Unauthenticated.',
        ]);
    }
}
