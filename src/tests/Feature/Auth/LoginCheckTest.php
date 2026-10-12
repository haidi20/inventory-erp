<?php

namespace Tests\Feature\Auth;

use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginCheckTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup initial roles for testing
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'dokter', 'guard_name' => 'web', 'label' => 'Dokter Spesialis']);
    }

    /** @test */
    public function it_returns_success_and_roles_for_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user->assignRole('admin');

        $response = $this->postJson('/login/pre-check', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'roles' => [
                    ['name' => 'admin', 'label' => 'admin']
                ],
            ]);
    }

    /** @test */
    public function it_identifies_multiple_roles_correctly()
    {
        $user = User::factory()->create([
            'email' => 'multi@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user->assignRole(['admin', 'dokter']);

        $response = $this->postJson('/login/pre-check', [
            'email' => 'multi@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $roles = $response->json('roles');
        
        $this->assertCount(2, $roles);
        $response->assertJsonFragment(['name' => 'admin']);
        $response->assertJsonFragment(['name' => 'dokter', 'label' => 'Dokter Spesialis']);
    }

    /** @test */
    public function it_returns_error_for_invalid_password()
    {
        User::factory()->create([
            'email' => 'wrong@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/login/pre-check', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Email atau password tidak valid.'
            ]);
    }

    /** @test */
    public function it_returns_error_for_non_existent_user()
    {
        $response = $this->postJson('/login/pre-check', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Email atau password tidak valid.'
            ]);
    }
}
