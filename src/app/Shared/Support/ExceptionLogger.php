<?php

namespace App\Shared\Support;

use App\Modules\Finance\Ledger\Models\AuditLog;
use App\Modules\Core\Authorization\Services\TelegramAlertService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ExceptionLogger
{
    public static function log(Throwable $e, string $class)
    {
        $message = $e->getMessage();
        $validationErrors = null;
        if ($e instanceof ValidationException) {
            $validationErrors = $e->errors();
        }

        $origin = self::resolveOrigin($e, $class);

        try {
            $user = Auth::user() ?? auth('api')->user();
            $branchUuid = $user?->branch_uuid ?? $user?->branch_uuid;
            $userContext = [
                'name' => $user?->name,
                'email' => $user?->email,
                'uuid' => $user?->uuid,
            ];
            $requestContext = [
                'method' => request()?->method(),
                'url' => request()?->fullUrl(),
                'ip' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ];

            // Jika branch_uuid tidak ditemukan, jangan simpan ke DB karena constraint NOT NULL
            if (! $branchUuid) {
                Log::info("AuditLog skipped in ExceptionLogger: branch_uuid is null. Message: {$message}");
            } else {
                AuditLog::create([
                    'branch_uuid' => $branchUuid,
                    'user_uuid' => $user?->uuid ?? auth('api')->id(),
                    'event' => 'EXCEPTION',
                    'entity_type' => $class,
                    'before' => [
                        'message' => $message,
                        'validation_errors' => $validationErrors,
                        'file' => $origin['file'],
                        'line' => $origin['line'],
                        'source_class' => $origin['class'],
                        'source_method' => $origin['method'],
                        'code' => $e->getCode(),
                        'user' => $userContext,
                        'request' => $requestContext,
                    ],
                    'after' => [
                        'trace' => substr($e->getTraceAsString(), 0, 1000),
                    ],
                    'ip_address' => $requestContext['ip'],
                    'user_agent' => $requestContext['user_agent'],
                ]);
            }

            app(TelegramAlertService::class)->sendFailureAlert([
                'event' => 'EXCEPTION',
                'class' => $class,
                'message' => $message,
                'validation_errors' => $validationErrors,
                'source' => $origin,
                'user' => $userContext,
                'request' => $requestContext,
            ]);
        } catch (Throwable $logError) {
            Log::error('Centralized Logging Failed: '.$logError->getMessage());
        }
    }

    private static function resolveOrigin(Throwable $e, string $fallbackClass): array
    {
        $fallback = [
            'class' => $fallbackClass,
            'method' => '-',
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        $trace = $e->getTrace();
        foreach ($trace as $frame) {
            $file = (string) ($frame['file'] ?? '');
            $class = (string) ($frame['class'] ?? '');

            if ($file === '' || !str_contains(str_replace('\\', '/', $file), '/src/app/')) {
                continue;
            }

            if (!str_starts_with($class, 'App\\')) {
                continue;
            }

            return [
                'class' => $class,
                'method' => (string) ($frame['function'] ?? '-'),
                'file' => $file,
                'line' => (int) ($frame['line'] ?? $e->getLine()),
            ];
        }

        foreach ($trace as $frame) {
            $file = (string) ($frame['file'] ?? '');
            if ($file === '') {
                continue;
            }

            if (!str_contains(str_replace('\\', '/', $file), '/src/app/')) {
                continue;
            }

            if (str_contains($file, 'ExceptionLogger.php')) {
                continue;
            }

            return [
                'class' => self::normalizeFrameClass((string) ($frame['class'] ?? ''), $fallbackClass, $file),
                'method' => (string) ($frame['function'] ?? '-'),
                'file' => $file,
                'line' => (int) ($frame['line'] ?? $e->getLine()),
            ];
        }

        return $fallback;
    }

    private static function normalizeFrameClass(string $frameClass, string $fallbackClass, string $file): string
    {
        if ($frameClass !== '' && str_starts_with($frameClass, 'App\\')) {
            return $frameClass;
        }

        $normalizedFile = str_replace('\\', '/', $file);
        $appPosition = strpos($normalizedFile, '/src/app/');
        if ($appPosition !== false) {
            $relative = substr($normalizedFile, $appPosition + strlen('/src/app/'));
            $relative = preg_replace('/\\.php$/', '', $relative);
            $relative = str_replace('/', '\\', (string) $relative);

            return 'App\\' . $relative;
        }

        return $fallbackClass;
    }
}
