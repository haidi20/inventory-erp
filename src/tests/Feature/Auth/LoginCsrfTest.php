<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;

class LoginCsrfTest extends TestCase
{
    /**
     * Test that a 419 error (TokenMismatchException) on login 
     * redirects back with a friendly error message instead of showing the default 419 page.
     */
    public function test_login_csrf_mismatch_redirects_back_with_message()
    {
        // We simulate a CSRF mismatch by throwing the exception in a test route
        // or by calling the login route without a token when CSRF is enabled.
        
        // However, a more direct way to test the EXCEPTION HANDLER in bootstrap/app.php
        // is to hit a route that throws TokenMismatchException.
        
        Route::post('/test-csrf-failure', function () {
            throw new TokenMismatchException();
        })->middleware('web');

        $response = $this->post('/test-csrf-failure');

        // Assert that we are redirected back to the previous page (or login if we set it)
        // Since there's no "previous" URL in this test request yet, we might need to set it.
        
        $response = $this->from('/login')->post('/test-csrf-failure');

        $response->assertRedirect('/login');
        $response->assertSessionHas('error', 'Sesi Anda telah berakhir. Silakan coba login kembali.');
    }

    /**
     * Test that a 419 error on JSON request returns a JSON response.
     */
    public function test_json_csrf_mismatch_returns_json_response()
    {
        Route::post('/test-csrf-json', function () {
            throw new TokenMismatchException();
        })->middleware('web');

        $response = $this->postJson('/test-csrf-json');

        $response->assertStatus(419);
        $response->assertJson([
            'message' => 'Sesi Anda telah berakhir. Silakan coba login kembali.'
        ]);
    }
}
