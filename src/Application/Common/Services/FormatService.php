<?php

declare(strict_types=1);

namespace Gazelle\Application\Common\Services;

/**
 * Format Service
 *
 * Application service for formatting data for display.
 * Replaces legacy Format class static methods.
 */
final readonly class FormatService
{
    private const SIZE_UNITS = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB'];

    /**
     * Format bytes into human-readable size
     * Replaces Format::get_size()
     */
    public function formatSize(int|float $bytes, int $precision = 2): string
    {
        if ($bytes < 0) {
            return '-' . $this->formatSize(abs($bytes), $precision);
        }

        if ($bytes === 0 || $bytes === 0.0) {
            return '0 B';
        }

        $exponent = min(
            (int) floor(log($bytes, 1024)),
            count(self::SIZE_UNITS) - 1
        );

        $size = $bytes / (1024 ** $exponent);

        return round($size, $precision) . ' ' . self::SIZE_UNITS[$exponent];
    }

    /**
     * Parse a size string into bytes
     * Replaces Format::get_bytes()
     */
    public function parseSize(string $size): int
    {
        $size = trim($size);

        if (!preg_match('/^([\d.]+)\s*([KMGTPE]?B?)$/i', $size, $matches)) {
            return 0;
        }

        $value = (float) $matches[1];
        $unit = strtoupper($matches[2] ?: 'B');

        $multipliers = [
            'B' => 1,
            'KB' => 1024,
            'MB' => 1024 ** 2,
            'GB' => 1024 ** 3,
            'TB' => 1024 ** 4,
            'PB' => 1024 ** 5,
            'EB' => 1024 ** 6,
        ];

        return (int) ($value * ($multipliers[$unit] ?? 1));
    }

    /**
     * Format a ratio value
     * Replaces Format::get_ratio() and Format::get_ratio_html()
     */
    public function formatRatio(float $ratio, bool $asHtml = false): string
    {
        if ($ratio === INF) {
            $text = '∞';
            $class = 'r99';
        } elseif ($ratio >= 5) {
            $text = sprintf('%.2f', $ratio);
            $class = 'r50';
        } elseif ($ratio >= 1) {
            $text = sprintf('%.2f', $ratio);
            $class = 'r10';
        } elseif ($ratio >= 0.5) {
            $text = sprintf('%.2f', $ratio);
            $class = 'r05';
        } else {
            $text = sprintf('%.2f', $ratio);
            $class = 'r00';
        }

        if (!$asHtml) {
            return $text;
        }

        return sprintf('<span class="ratio %s">%s</span>', $class, $text);
    }

    /**
     * Calculate ratio from upload/download
     */
    public function calculateRatio(int $uploaded, int $downloaded): float
    {
        if ($downloaded === 0) {
            return $uploaded > 0 ? INF : 0.0;
        }

        return $uploaded / $downloaded;
    }

    /**
     * Format a time ago string
     * Replaces time_diff() and Format::time_diff()
     */
    public function formatTimeAgo(
        \DateTimeInterface $time,
        int $levels = 2,
        bool $span = true
    ): string {
        $now = new \DateTimeImmutable();
        $diff = $now->diff($time);

        $parts = [];

        if ($diff->y > 0) {
            $parts[] = $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
        }
        if ($diff->d > 0) {
            $parts[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
        }
        if ($diff->h > 0) {
            $parts[] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
        }
        if ($diff->i > 0) {
            $parts[] = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        }
        if ($diff->s > 0 && count($parts) === 0) {
            $parts[] = $diff->s . ' second' . ($diff->s > 1 ? 's' : '');
        }

        if (count($parts) === 0) {
            $text = 'Just now';
        } else {
            $text = implode(', ', array_slice($parts, 0, $levels)) . ' ago';
        }

        if (!$span) {
            return $text;
        }

        return sprintf(
            '<span class="time" title="%s">%s</span>',
            $time->format('Y-m-d H:i:s'),
            $text
        );
    }

    /**
     * Format a number with commas
     * Replaces Format::number()
     */
    public function formatNumber(int|float $number, int $decimals = 0): string
    {
        return number_format($number, $decimals, '.', ',');
    }

    /**
     * Format a percentage
     */
    public function formatPercentage(float $value, int $decimals = 2): string
    {
        return number_format($value * 100, $decimals) . '%';
    }

    /**
     * Escape string for HTML display
     * Replaces display_str()
     */
    public function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Truncate string with ellipsis
     */
    public function truncate(
        string $text,
        int $length,
        string $suffix = '...'
    ): string {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length - mb_strlen($suffix)) . $suffix;
    }

    /**
     * Convert newlines to HTML breaks
     */
    public function nl2br(string $text): string
    {
        return nl2br($this->escapeHtml($text), false);
    }

    /**
     * Format a torrent name for display
     */
    public function formatTorrentName(
        string $artist,
        string $title,
        ?string $year = null,
        ?string $format = null,
        ?string $encoding = null
    ): string {
        $parts = [$artist, '-', $title];

        if ($year !== null) {
            $parts[] = "($year)";
        }

        if ($format !== null) {
            $parts[] = "[$format]";
        }

        if ($encoding !== null) {
            $parts[] = '{' . $encoding . '}';
        }

        return implode(' ', $parts);
    }

    /**
     * Format seed time
     */
    public function formatSeedTime(int $seconds): string
    {
        if ($seconds < 3600) {
            return sprintf('%d min', $seconds / 60);
        }

        if ($seconds < 86400) {
            return sprintf('%.1f hours', $seconds / 3600);
        }

        return sprintf('%.1f days', $seconds / 86400);
    }
}
