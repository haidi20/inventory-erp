<?php

namespace App\Modules\Core\Identity\Http\Controllers;

use App\Modules\Core\Identity\Services\WebAuthService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WebAuthController extends Controller
{
    public function __construct(
        private WebAuthService $webAuthService
    ) {
        parent::__construct();
    }

    public function showLogin()
    {
        $company = $this->webAuthService->getFirstActiveCompany();

        return Inertia::render('Core/Identity/presentations/views/Login', [
            'company' => $company?->toArray(),
            'status' => session('status'),
            'errors' => $this->inertiaErrors(),
        ])->rootView('auth.layout');
    }

    public function showRegister()
    {
        return Inertia::render('Core/Identity/presentations/views/Register', [
            'timezones' => $this->webAuthService->getTimezones(),
            'errors' => $this->inertiaErrors(),
        ])->rootView('auth.layout');
    }

    private function extractCredentials(Request $request): array
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $fieldName = 'email';
        if ($request->filled('email')) {
            $identifier = (string) $request->input('email');
        } elseif ($request->filled('username')) {
            $fieldName = 'username';
            $identifier = (string) $request->input('username');
        } else {
            $request->validate([
                'email' => 'required|string',
            ]);
            $identifier = '';
        }

        return [trim($identifier), $fieldName];
    }

    public function preCheck(Request $request)
    {
        [$identifier] = $this->extractCredentials($request);
        $user = $this->webAuthService->findUser($identifier);
        $password = (string) $request->input('password');

        if (! $user || ! $this->webAuthService->validateCredentials($user, $password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password tidak valid.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'roles' => collect($user->roles),
        ]);
    }

    public function login(Request $request)
    {
        [, $fieldName] = $this->extractCredentials($request);
        $identifier = trim($request->input($fieldName));
        $user = $this->webAuthService->findUser($identifier);
        if (! $user) {
            return back()->withErrors([
                $fieldName => 'Email atau password tidak valid.',
            ])->onlyInput($fieldName);
        }

        $authUser = $this->webAuthService->attemptLogin(
            $user,
            (string) $request->input('password'),
            $request->boolean('remember')
        );

        if (! $authUser) {
            return back()->withErrors([
                $fieldName => 'Email atau password tidak valid.',
            ])->onlyInput($fieldName);
        }

        if (! $authUser->is_active) {
            $this->webAuthService->logout();

            return back()->with('error', 'Akun Anda tidak aktif.')->onlyInput($fieldName);
        }

        $request->session()->regenerate();

        $token = $this->webAuthService->issueApiToken($authUser);
        if ($token) {
            $request->session()->put('api_token', $token);
        }

        return redirect()->to($this->webAuthService->resolveLandingUrl($authUser));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
            'company_name' => ['required', 'string', 'max:255', 'unique:companies,name'],
            'timezone' => ['required', 'string', 'max:64'],
        ]);

        if (! in_array($validated['timezone'], $this->webAuthService->getTimezones(), true)) {
            return back()
                ->withErrors(['timezone' => 'Zona waktu tidak valid.'])
                ->onlyInput(['name', 'email', 'username', 'company_name']);
        }

        $user = $this->webAuthService->register($validated);
        $authUser = $this->webAuthService->loginRegisteredUser($user->uuid);

        if (! $authUser) {
            return back()->withErrors(['email' => 'Registrasi berhasil, tetapi sesi gagal dibuat.']);
        }

        $request->session()->regenerate();

        $token = $this->webAuthService->issueApiToken($authUser);
        if ($token) {
            $request->session()->put('api_token', $token);
        }

        return redirect()->intended('/');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        $this->webAuthService->updatePassword($request->user()->uuid, $request->password);

        return back()->with('cp_success', 'Password berhasil diubah.');
    }

    public function logout(Request $request)
    {
        $this->webAuthService->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function inertiaErrors(): array
    {
        return session('errors')?->getBag('default')->getMessages() ?? [];
    }
}
