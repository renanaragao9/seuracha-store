<?php

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_email_and_password(): void
    {
        $user = User::create([
            'name' => 'Administrador',
            'email' => 'admin@seuracha.com',
            'password' => '12345678',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'admin@seuracha.com',
            'password' => '12345678',
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', 'admin@seuracha.com')
            ->assertJsonStructure([
                'data' => ['user' => ['id', 'name', 'email', 'phone', 'image_url'], 'token'],
            ]);
    }

    public function test_user_can_login_with_valid_phone_and_password(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@seuracha.com',
            'phone' => '11999999999',
            'password' => '12345678',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'phone' => '11999999999',
            'password' => '12345678',
        ]);

        $response->assertOk()->assertJson(['status' => 'success']);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@seuracha.com',
            'password' => '12345678',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'admin@seuracha.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Email ou senha inválidos.',
            ]);
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'nao-existe@seuracha.com',
            'password' => '12345678',
        ]);

        $response->assertStatus(401)
            ->assertJson(['status' => 'error']);
    }

    public function test_login_requires_password(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'admin@seuracha.com',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_login_without_email_or_phone_fails_authentication(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'password' => '12345678',
        ]);

        $response->assertStatus(401)
            ->assertJson(['status' => 'error']);
    }

    public function test_authenticated_user_can_access_me_endpoint(): void
    {
        $user = User::create([
            'name' => 'Administrador',
            'email' => 'admin@seuracha.com',
            'password' => '12345678',
            'status' => 'active',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me');

        $response->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', 'admin@seuracha.com');
    }

    public function test_guest_cannot_access_me_endpoint(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::create([
            'name' => 'Administrador',
            'email' => 'admin@seuracha.com',
            'password' => '12345678',
            'status' => 'active',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/logout');

        $response->assertOk()->assertJson(['status' => 'success']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_guest_cannot_logout(): void
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401);
    }
}
