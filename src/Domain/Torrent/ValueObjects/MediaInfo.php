<?php

declare(strict_types=1);

namespace Gazelle\Domain\Torrent\ValueObjects;

/**
 * Media Format Enum
 */
enum MediaFormat: string
{
    case FLAC = 'FLAC';
    case MP3 = 'MP3';
    case AAC = 'AAC';
    case AC3 = 'AC3';
    case DTS = 'DTS';
    case OGG = 'OGG';
    case ALAC = 'ALAC';

    public function isLossless(): bool
    {
        return match ($this) {
            self::FLAC, self::ALAC => true,
            default => false,
        };
    }

    public function label(): string
    {
        return $this->value;
    }
}
