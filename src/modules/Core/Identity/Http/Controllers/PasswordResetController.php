<?php

namespace App\Modules\Core\Identity\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Http\Response;

class PasswordResetController extends Controller
{
    // ─── Forgot Password ─────────────────────────────────────────────────────

    public function showForgot(Request $request): Response
    {
        return Inertia::render('Core/Identity/presentations/views/ForgotPassword')
            ->toResponse($request)
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
    }

    public function sendLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Link reset password telah dikirim ke email Anda.')
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    // ─── Reset Password ───────────────────────────────────────────────────────

    public function showReset(Request $request, string $token): Response
    {
        return Inertia::render('Core/Identity/presentations/views/ResetPassword', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ])
            ->toResponse($request)
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Password berhasil direset. Silakan login.')
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }
}
