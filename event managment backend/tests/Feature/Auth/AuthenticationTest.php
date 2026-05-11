<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_existing_users_can_authenticate_using_email_only(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/login', [
            'email' => $user->email,
        ]);

        $this->assertAuthenticatedAs($user);
        $response
            ->assertOk()
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_a_user_is_auto_created_and_authenticated_when_email_does_not_exist(): void
    {
        $response = $this->postJson('/login', [
            'email' => 'newuser@example.com',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'provider' => 'email',
        ]);
        $response
            ->assertOk()
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonPath('user.email', 'newuser@example.com');
    }

    public function test_email_is_required_for_email_only_login(): void
    {
        $response = $this->postJson('/login', [
            'email' => 'not-an-email',
        ]);

        $this->assertGuest();
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}