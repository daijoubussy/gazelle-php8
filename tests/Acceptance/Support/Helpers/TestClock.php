<?php

declare(strict_types=1);

namespace Gazelle\Tests\Acceptance\Support\Helpers;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * Deterministic clock for testing
 *
 * Provides a frozen, controllable clock that eliminates time-dependent test flakiness.
 * All code under test should use this clock instead of date()/time()/new DateTime().
 */
final class TestClock
{
    private DateTimeImmutable $frozenTime;
    private string $defaultTime;

    public function __construct(string $defaultTime = '2024-01-15 10:00:00')
    {
        $this->defaultTime = $defaultTime;
        $this->frozenTime = new DateTimeImmutable($defaultTime);
    }

    /**
     * Freeze time at a specific moment
     */
    public function freeze(string $datetime): void
    {
        $this->frozenTime = new DateTimeImmutable($datetime);
    }

    /**
     * Get current frozen time
     */
    public function now(): DateTimeImmutable
    {
        return $this->frozenTime;
    }

    /**
     * Get Unix timestamp of frozen time
     */
    public function timestamp(): int
    {
        return $this->frozenTime->getTimestamp();
    }

    /**
     * Get formatted date string
     */
    public function format(string $format): string
    {
        return $this->frozenTime->format($format);
    }

    /**
     * Advance time by seconds
     */
    public function advance(int $seconds): void
    {
        $this->frozenTime = $this->frozenTime->modify("+{$seconds} seconds");
    }

    /**
     * Advance time by minutes
     */
    public function advanceMinutes(int $minutes): void
    {
        $this->frozenTime = $this->frozenTime->modify("+{$minutes} minutes");
    }

    /**
     * Advance time by hours
     */
    public function advanceHours(int $hours): void
    {
        $this->frozenTime = $this->frozenTime->modify("+{$hours} hours");
    }

    /**
     * Advance time by days
     */
    public function advanceDays(int $days): void
    {
        $this->frozenTime = $this->frozenTime->modify("+{$days} days");
    }

    /**
     * Reset to default frozen time
     */
    public function reset(): void
    {
        $this->frozenTime = new DateTimeImmutable($this->defaultTime);
    }

    /**
     * Check if a datetime is in the past relative to frozen time
     */
    public function isPast(DateTimeInterface $datetime): bool
    {
        return $datetime < $this->frozenTime;
    }

    /**
     * Check if a datetime is in the future relative to frozen time
     */
    public function isFuture(DateTimeInterface $datetime): bool
    {
        return $datetime > $this->frozenTime;
    }

    /**
     * Get difference in seconds from frozen time to given datetime
     */
    public function diffSeconds(DateTimeInterface $datetime): int
    {
        return $datetime->getTimestamp() - $this->frozenTime->getTimestamp();
    }
}
