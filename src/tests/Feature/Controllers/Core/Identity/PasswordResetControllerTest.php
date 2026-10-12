<?php

namespace Tests\Feature\Controllers\Core\Identity;

use App\Modules\Core\Identity\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PasswordResetControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['inertia.testing.ensure_pages_exist' => false]);
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);
    }

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Core/Identity/presentations/views/ForgotPassword'));
    }

    public function test_send_link_creates_token_and_sends_reset_email(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_screen_accepts_token_and_email(): void
    {
        $token = 'reset-token';

        $this->get("/reset-password/{$token}?email=test@example.com")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Core/Identity/presentations/views/ResetPassword')
                ->where('token', $token)
                ->where('email', 'test@example.com'));
    }

    public function test_password_reset_updates_password_and_allows_login(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);
        $notification = Notification::sent($user, ResetPassword::class)->first();
        $this->assertNotNull($notification);

        $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-password-2026',
            'password_confirmation' => 'new-password-2026',
        ])->assertRedirect('/login')->assertSessionHas('status');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'new-password-2026',
        ]);

        $this->assertAuthenticatedAs($user);
    }
}
