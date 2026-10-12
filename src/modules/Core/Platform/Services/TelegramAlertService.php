<?php

namespace App\Modules\Core\Platform\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramAlertService
{
    public function sendFailureAlert(array $context): void
    {
        $token = (string) config('services.telegram.bot_token', '');
        $chatId = $this->resolveChatId($token);

        if ($token === '' || $chatId === '') {
            Log::info('Telegram alert skipped: bot token or chat id is missing.');

            return;
        }

        $message = $this->buildMessage($context);

        try {
            Http::timeout(10)
                ->asForm()
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                ])
                ->throw();
        } catch (Throwable $e) {
            Log::error('Telegram alert failed: '.$e->getMessage());
        }
    }

    private function buildMessage(array $context): string
    {
        $user = $context['user'] ?? [];
        $request = $context['request'] ?? [];
        $source = $context['source'] ?? [];
        $validationErrors = $context['validation_errors'] ?? [];
        $validationSummary = $this->formatValidationErrors($validationErrors);

        $lines = [
            'KLINIK ALERT',
            'Status: FAILED',
            'Event: '.($context['event'] ?? 'EXCEPTION'),
            'Class: '.($context['class'] ?? '-'),
            'Source Class: '.($source['class'] ?? '-'),
            'Source Method: '.($source['method'] ?? '-'),
            'Source Line: '.($source['line'] ?? '-'),
            // 'Source File: '.($source['file'] ?? '-'),
            'Message: '.($context['message'] ?? '-'),
            'User: '.($user['name'] ?? 'guest'),
            'Email: '.($user['email'] ?? '-'),
            // 'User UUID: ' . ($user['uuid'] ?? '-'),
            'HTTP Method: '.($request['method'] ?? '-'),
            'URL: '.($request['url'] ?? '-'),
            'IP: '.($request['ip'] ?? '-'),
        ];

        if ($validationSummary !== '') {
            $lines[] = 'Validation: '.$validationSummary;
        }

        return implode("\n", $lines);
    }

    private function formatValidationErrors(array $validationErrors): string
    {
        if ($validationErrors === []) {
            return '';
        }

        $parts = [];
        foreach ($validationErrors as $field => $messages) {
            $parts[] = $field.': '.implode(', ', (array) $messages);
        }

        return implode(' | ', $parts);
    }

    private function resolveChatId(string $token): string
    {
        if ($token === '') {
            return '';
        }

        $configuredChatId = (string) config('services.telegram.chat_id', '');
        if ($configuredChatId !== '') {
            return $configuredChatId;
        }

        try {
            $response = Http::timeout(10)
                ->get("https://api.telegram.org/bot{$token}/getUpdates")
                ->throw()
                ->json();

            $updates = $response['result'] ?? [];
            if ($updates === []) {
                return '';
            }

            $latest = end($updates);

            return (string) data_get($latest, 'message.chat.id', '');
        } catch (Throwable $e) {
            Log::error('Telegram chat id resolution failed: '.$e->getMessage());

            return '';
        }
    }
}
