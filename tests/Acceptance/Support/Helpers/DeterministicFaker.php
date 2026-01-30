<?php

declare(strict_types=1);

namespace Gazelle\Tests\Acceptance\Support\Helpers;

use Faker\Factory;
use Faker\Generator;

/**
 * Deterministic Faker wrapper for reproducible test data
 *
 * Always produces the same sequence of random values when using the same seed.
 * This eliminates test flakiness from random data generation.
 */
final class DeterministicFaker
{
    private Generator $faker;
    private int $seed;

    public function __construct(int $seed = 12345)
    {
        $this->seed = $seed;
        $this->faker = Factory::create();
        $this->faker->seed($seed);
    }

    /**
     * Reset faker to initial seed
     */
    public function reset(): void
    {
        $this->faker->seed($this->seed);
    }

    /**
     * Set a new seed
     */
    public function setSeed(int $seed): void
    {
        $this->seed = $seed;
        $this->faker->seed($seed);
    }

    /**
     * Get underlying faker generator
     */
    public function generator(): Generator
    {
        return $this->faker;
    }

    // ========================================
    // User Data Generation
    // ========================================

    public function username(): string
    {
        return $this->faker->userName();
    }

    public function email(): string
    {
        return $this->faker->safeEmail();
    }

    public function password(): string
    {
        return $this->faker->password(12, 20);
    }

    public function ipAddress(): string
    {
        return $this->faker->ipv4();
    }

    public function userAgent(): string
    {
        return $this->faker->userAgent();
    }

    // ========================================
    // Torrent Data Generation
    // ========================================

    public function torrentName(): string
    {
        $artist = $this->faker->name();
        $album = $this->faker->words(3, true);
        $year = $this->faker->year();
        return "{$artist} - {$album} ({$year}) [FLAC]";
    }

    public function infoHash(): string
    {
        return $this->faker->sha1();
    }

    public function fileSize(): int
    {
        return $this->faker->numberBetween(1_000_000, 10_000_000_000);
    }

    public function category(): string
    {
        return $this->faker->randomElement([
            'Music', 'Applications', 'E-Books', 'Audiobooks',
            'E-Learning Videos', 'Comedy', 'Comics', 'Misc'
        ]);
    }

    public function format(): string
    {
        return $this->faker->randomElement(['FLAC', 'MP3', 'AAC', 'Ogg Vorbis']);
    }

    public function bitrate(): string
    {
        return $this->faker->randomElement(['Lossless', '320', 'V0', 'V2', '256', '192']);
    }

    public function media(): string
    {
        return $this->faker->randomElement(['CD', 'WEB', 'Vinyl', 'Soundboard', 'DVD']);
    }

    // ========================================
    // Forum Data Generation
    // ========================================

    public function forumTitle(): string
    {
        return $this->faker->sentence(4);
    }

    public function forumPost(): string
    {
        return $this->faker->paragraphs(3, true);
    }

    public function bbCodePost(): string
    {
        $text = $this->faker->paragraph();
        $bold = $this->faker->word();
        $italic = $this->faker->word();
        return "[b]{$bold}[/b] {$text} [i]{$italic}[/i]";
    }

    // ========================================
    // Date/Time Generation (Deterministic)
    // ========================================

    public function pastDate(string $max = '-1 day'): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromMutable(
            $this->faker->dateTimeBetween('-1 year', $max)
        );
    }

    public function futureDate(string $min = '+1 day'): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromMutable(
            $this->faker->dateTimeBetween($min, '+1 year')
        );
    }

    public function recentDate(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromMutable(
            $this->faker->dateTimeBetween('-7 days', 'now')
        );
    }

    // ========================================
    // Numeric Generation
    // ========================================

    public function positiveInt(int $max = PHP_INT_MAX): int
    {
        return $this->faker->numberBetween(1, $max);
    }

    public function percentage(): float
    {
        return round($this->faker->randomFloat(2, 0, 100), 2);
    }

    public function ratio(): float
    {
        return round($this->faker->randomFloat(2, 0, 10), 2);
    }

    public function bytes(): int
    {
        return $this->faker->numberBetween(0, 1_000_000_000_000);
    }

    // ========================================
    // Boolean Generation
    // ========================================

    public function boolean(int $chanceOfGettingTrue = 50): bool
    {
        return $this->faker->boolean($chanceOfGettingTrue);
    }

    // ========================================
    // Array/Collection Generation
    // ========================================

    /**
     * @template T
     * @param array<T> $array
     * @return T
     */
    public function randomElement(array $array): mixed
    {
        return $this->faker->randomElement($array);
    }

    /**
     * @template T
     * @param array<T> $array
     * @return array<T>
     */
    public function randomElements(array $array, int $count): array
    {
        return $this->faker->randomElements($array, $count);
    }

    /**
     * @param callable(): mixed $generator
     * @return array<mixed>
     */
    public function many(callable $generator, int $count): array
    {
        $items = [];
        for ($i = 0; $i < $count; $i++) {
            $items[] = $generator();
        }
        return $items;
    }
}
