<?php

namespace App\Http\Resources\Concerns;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Throwable;

trait HasFormattedTimestamps
{
    protected function formatPayloadTimestamps(mixed $payload): mixed
    {
        if (is_array($payload)) {
            $formatted = [];
            foreach ($payload as $key => $value) {
                $formatted[$key] = $this->formatPayloadTimestamps($value);
            }

            $labels = [];
            foreach ($payload as $key => $value) {
                if (!is_string($key)) {
                    continue;
                }

                if (!$this->shouldFormatTimestampKey($key)) {
                    continue;
                }

                $timestamp = $this->normalizeTimestampValue($value);
                if ($timestamp === null) {
                    continue;
                }

                $isFullDateTime = str_ends_with(strtolower($key), '_at');

                $labels[$key . '_formatted'] = $this->formatDateShortIndonesia($timestamp, $isFullDateTime);
                $labels[$key . '_formatted_long'] = $this->formatDateLongIndonesia($timestamp, $isFullDateTime);
            }

            return array_merge($formatted, $labels);
        }

        return $payload;
    }

    protected function shouldFormatTimestampKey(string $key): bool
    {
        $normalizedKey = strtolower($key);
        if (str_ends_with($normalizedKey, '_formatted') || str_ends_with($normalizedKey, '_formatted_long')) {
            return false;
        }

        if (str_ends_with($normalizedKey, '_at') || str_ends_with($normalizedKey, '_date')) {
            return true;
        }

        return in_array($normalizedKey, ['date', 'dob', 'birth_date'], true);
    }

    protected function normalizeTimestampValue(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->timestamp;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->getTimestamp();
        }

        if (is_int($value) || is_float($value) || (is_string($value) && is_numeric($value))) {
            $timestamp = (int) $value;
            if ($timestamp > 9999999999) {
                $timestamp = (int) floor($timestamp / 1000);
            }

            return $timestamp >= 0 ? $timestamp : null;
        }

        if (is_string($value)) {
            try {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
                    return CarbonImmutable::createFromFormat('Y-m-d', $value, 'Asia/Makassar')
                        ->startOfDay()
                        ->timestamp;
                }

                return CarbonImmutable::parse($value, 'Asia/Makassar')->timestamp;
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

    protected function formatDateShortIndonesia(int $timestamp, bool $withTime = false): string
    {
        $format = $withTime ? 'd-m-Y H:i:s' : 'd-m-Y';
        return CarbonImmutable::createFromTimestamp($timestamp, 'Asia/Makassar')
            ->locale('id')
            ->translatedFormat($format);
    }

    protected function formatDateLongIndonesia(int $timestamp, bool $withTime = false): string
    {
        $format = $withTime ? 'd F Y H:i:s' : 'd F Y';
        return CarbonImmutable::createFromTimestamp($timestamp, 'Asia/Makassar')
            ->locale('id')
            ->translatedFormat($format);
    }
}
