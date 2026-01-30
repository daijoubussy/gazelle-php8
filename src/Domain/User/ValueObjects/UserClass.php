<?php

declare(strict_types=1);

namespace Gazelle\Domain\User\ValueObjects;

/**
 * User Class Enum
 *
 * Represents user permission levels in the tracker.
 */
enum UserClass: int
{
    case User = 1;
    case Member = 2;
    case PowerUser = 3;
    case Elite = 4;
    case TorrentMaster = 5;
    case VIP = 6;
    case Legend = 7;
    case Moderator = 100;
    case Administrator = 200;
    case SysOp = 255;

    /**
     * Get the display name
     */
    public function label(): string
    {
        return match ($this) {
            self::User => 'User',
            self::Member => 'Member',
            self::PowerUser => 'Power User',
            self::Elite => 'Elite',
            self::TorrentMaster => 'Torrent Master',
            self::VIP => 'VIP',
            self::Legend => 'Legend',
            self::Moderator => 'Moderator',
            self::Administrator => 'Administrator',
            self::SysOp => 'SysOp',
        };
    }

    /**
     * Check if this class is staff level
     */
    public function isStaff(): bool
    {
        return $this->value >= self::Moderator->value;
    }

    /**
     * Check if this class can perform an action
     */
    public function canPerform(UserClass $requiredClass): bool
    {
        return $this->value >= $requiredClass->value;
    }

    /**
     * Get minimum upload requirement in bytes
     */
    public function uploadRequirement(): int
    {
        return match ($this) {
            self::User => 0,
            self::Member => 10 * 1024 * 1024 * 1024,        // 10 GB
            self::PowerUser => 25 * 1024 * 1024 * 1024,     // 25 GB
            self::Elite => 100 * 1024 * 1024 * 1024,        // 100 GB
            self::TorrentMaster => 500 * 1024 * 1024 * 1024, // 500 GB
            self::VIP, self::Legend, self::Moderator, self::Administrator, self::SysOp => 0,
        };
    }

    /**
     * Get minimum ratio requirement
     */
    public function ratioRequirement(): float
    {
        return match ($this) {
            self::User => 0.0,
            self::Member => 0.65,
            self::PowerUser => 1.05,
            self::Elite => 1.05,
            self::TorrentMaster => 1.05,
            self::VIP, self::Legend, self::Moderator, self::Administrator, self::SysOp => 0.0,
        };
    }
}
