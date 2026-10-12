<?php

namespace Tests\Feature\Api;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use Tests\Traits\ManualDatabaseSetup;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthRouteTest extends TestCase
{
    use ManualDatabaseSetup;

    protected User $user;
    protected string $password = 'password123';

    protected function setUp(): void
    {
        parent::setUp();
        
        $branch = Branch::create([
            'uuid' => Str::uuid(),
            'short_name' => 'TST',
            'name' => 'Test Branch',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'name' => 'API Test User',
            'email' => 'api_test@example.com',
            'password' => bcrypt($this->password),
            'branch_uuid' => $branch->uuid,
        ]);
    }

    public function test_api_login_success()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'api_test@example.com',
            'password' => $this->password,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'access_token',
                     'token_type',
                     'expires_in',
                     'user' => [
                         'uuid',
                         'email',
                     ]
                 ]);
    }

    public function test_api_login_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'api_test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJsonStructure(['error', 'messages']);
    }

    public function test_api_me_route()
    {
        $token = auth('api')->attempt(['email' => $this->user->email, 'password' => $this->password]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
                 ->assertJsonPath('email', $this->user->email);
    }

    public function test_api_logout_route()
    {
        $token = auth('api')->attempt(['email' => $this->user->email, 'password' => $this->password]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'User successfully signed out']);

        // Token should be invalidated
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/auth/me');

        $response2->assertStatus(401);
    }

    public function test_api_refresh_route()
    {
        $token = auth('api')->attempt(['email' => $this->user->email, 'password' => $this->password]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/refresh');

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token']);

        $newToken = $response->json('access_token');
        $this->assertNotEquals($token, $newToken);
    }
}
