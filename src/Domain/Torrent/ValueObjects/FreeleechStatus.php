<?php

declare(strict_types=1);

namespace Gazelle\Domain\Torrent\ValueObjects;

/**
 * Freeleech Status Enum
 */
enum FreeleechStatus: string
{
    case Normal = 'normal';
    case Free = 'free';
    case Neutral = 'neutral';
    case HalfLeech = 'half';

    /**
     * Get the download multiplier
     */
    public function downloadMultiplier(): float
    {
        return match ($this) {
            self::Normal, self::HalfLeech => 1.0,
            self::Free, self::Neutral => 0.0,
        };
    }

    /**
     * Get the upload multiplier
     */
    public function uploadMultiplier(): float
    {
        return match ($this) {
            self::Normal, self::Free, self::HalfLeech => 1.0,
            self::Neutral => 0.0,
        };
    }

    /**
     * Check if download counts against ratio
     */
    public function countsDownload(): bool
    {
        return match ($this) {
            self::Normal, self::HalfLeech => true,
            self::Free, self::Neutral => false,
        };
    }

    /**
     * Check if upload counts toward ratio
     */
    public function countsUpload(): bool
    {
        return match ($this) {
            self::Normal, self::Free, self::HalfLeech => true,
            self::Neutral => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Normal => 'Normal',
            self::Free => 'Freeleech',
            self::Neutral => 'Neutral Leech',
            self::HalfLeech => '50% Freeleech',
        };
    }
}
