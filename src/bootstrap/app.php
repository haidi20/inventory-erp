<?php

use App\Http\Middleware\CheckApiPermission;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\NoCacheMiddleware;
use App\Shared\Http\Middleware\ResolveCompanyContext;
use App\Shared\Support\ExceptionLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            NoCacheMiddleware::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            ResolveCompanyContext::class,
        ]);

        $middleware->api(append: [
            ResolveCompanyContext::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => CheckApiPermission::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function ($response, \Throwable $e, Request $request) {
            if (method_exists($response, 'getStatusCode') && $response->getStatusCode() === 419) {
                $message = 'Sesi Anda telah berakhir. Silakan coba login kembali.';

                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['message' => $message], 419);
                }

                return redirect()
                    ->back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->with('error', $message);
            }

            return $response;
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            ExceptionLogger::log($e, 'HttpValidation');
            return null;
        });

        $exceptions->render(function (AuthorizationException|UnauthorizedException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }

            $previousUrl = url()->previous();
            $currentUrl = $request->fullUrl();
            $fallbackUrl = route('dashboard');
            $targetUrl = ($previousUrl && $previousUrl !== $currentUrl)
                ? $previousUrl
                : $fallbackUrl;

            return redirect()->to($targetUrl)->with('error', 'Anda tidak memiliki hak akses untuk membuka halaman tersebut.');
        });
    })->create();
