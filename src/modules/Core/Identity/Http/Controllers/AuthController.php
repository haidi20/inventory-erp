<?php

namespace App\Modules\Core\Identity\Http\Controllers;

use App\Shared\Support\ExceptionLogger;
use App\Modules\Core\Identity\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Core\Identity\Services\AuthService;
use App\Modules\Core\Identity\Entities\LoginEntity;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            ExceptionLogger::log(
                ValidationException::withMessages($validator->errors()->toArray()),
                self::class
            );
            return response()->json($validator->errors(), 422);
        }

        $entity = new LoginEntity($request->email, $request->password);

        try {
            $response = $this->authService->login($entity);
            return response()->json($this->formatApiPayload($response));
        } catch (ValidationException $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json(['error' => 'Unauthorized', 'messages' => $e->errors()], 401);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->authService->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            return response()->json($this->formatApiPayload($this->authService->refresh()));
        } catch (\Exception $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json(['error' => 'Could not refresh token'], 401);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        try {
            return response()->json($this->formatApiPayload($this->authService->me()));
        } catch (ValidationException $e) {
            ExceptionLogger::log($e, self::class);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }
}
