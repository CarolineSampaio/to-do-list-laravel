<?php

namespace Tests\Feature;

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
}
