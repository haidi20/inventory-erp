<?php

namespace App\Shared\Traits;

use Illuminate\Support\Carbon;

/**
 * HasFormattedTimestamps
 * 
 * Trait untuk mempermudah pemformatan Unix Timestamp (integer) di database
 * menjadi format tanggal yang manusiawi untuk tampilan frontend/API.
 */
trait HasFormattedTimestamps
{
    /**
     * Format timestamp ke format pendek (DD-MM-YYYY)
     */
    public function formatShortDate(?int $timestamp): ?string
    {
        if (!$timestamp) return null;
        return Carbon::createFromTimestamp($timestamp)->format('d-m-Y');
    }

    /**
     * Format timestamp ke format panjang Indonesia (D F Y)
     */
    public function formatFullDate(?int $timestamp): ?string
    {
        if (!$timestamp) return null;
        return Carbon::createFromTimestamp($timestamp)
            ->locale('id')
            ->translatedFormat('d F Y');
    }

    /**
     * Format timestamp ke datetime pendek (DD-MM-YYYY HH:mm)
     */
    public function formatShortDateTime(?int $timestamp): ?string
    {
        if (!$timestamp) return null;
        return Carbon::createFromTimestamp($timestamp)->format('d-m-Y H:i');
    }

    /**
     * Format timestamp ke datetime panjang Indonesia (D F Y HH:mm)
     */
    public function formatFullDateTime(?int $timestamp): ?string
    {
        if (!$timestamp) return null;
        return Carbon::createFromTimestamp($timestamp)
            ->locale('id')
            ->translatedFormat('d F Y H:i');
    }

    /**
     * Format timestamp kustom
     */
    public function formatTimestamp(?int $timestamp, string $format = 'Y-m-d H:i:s'): ?string
    {
        if (!$timestamp) return null;
        return Carbon::createFromTimestamp($timestamp)->format($format);
    }
}
