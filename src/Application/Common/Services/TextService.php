<?php

declare(strict_types=1);

namespace Gazelle\Application\Common\Services;

/**
 * Text Service
 *
 * Application service for text processing and BBCode.
 * Replaces legacy Text class.
 */
final class TextService
{
    /** @var array<string, callable> */
    private array $bbcodeTags = [];

    public function __construct()
    {
        $this->registerDefaultTags();
    }

    /**
     * Convert BBCode to HTML
     * Replaces Text::full_format()
     */
    public function bbcodeToHtml(string $text): string
    {
        // Escape HTML first
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Process BBCode tags
        $text = $this->processBbcode($text);

        // Convert newlines
        $text = nl2br($text, false);

        return $text;
    }

    /**
     * Strip BBCode tags
     */
    public function stripBbcode(string $text): string
    {
        return preg_replace('/\[.+?\]/', '', $text) ?? $text;
    }

    /**
     * Parse BBCode but return plain text
     */
    public function bbcodeToText(string $text): string
    {
        // Strip tags
        $text = $this->stripBbcode($text);

        // Decode entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($text);
    }

    /**
     * Register a custom BBCode tag handler
     */
    public function registerTag(string $tag, callable $handler): void
    {
        $this->bbcodeTags[$tag] = $handler;
    }

    /**
     * Process BBCode in text
     */
    private function processBbcode(string $text): string
    {
        // Simple tags: [b], [i], [u], [s]
        $text = preg_replace(
            '/\[b\](.*?)\[\/b\]/is',
            '<strong>$1</strong>',
            $text
        ) ?? $text;

        $text = preg_replace(
            '/\[i\](.*?)\[\/i\]/is',
            '<em>$1</em>',
            $text
        ) ?? $text;

        $text = preg_replace(
            '/\[u\](.*?)\[\/u\]/is',
            '<span style="text-decoration: underline;">$1</span>',
            $text
        ) ?? $text;

        $text = preg_replace(
            '/\[s\](.*?)\[\/s\]/is',
            '<span style="text-decoration: line-through;">$1</span>',
            $text
        ) ?? $text;

        // [url] tag
        $text = preg_replace_callback(
            '/\[url=([^\]]+)\](.*?)\[\/url\]/is',
            fn($m) => sprintf(
                '<a href="%s" rel="nofollow noopener" target="_blank">%s</a>',
                $this->sanitizeUrl($m[1]),
                $m[2]
            ),
            $text
        ) ?? $text;

        $text = preg_replace_callback(
            '/\[url\](.*?)\[\/url\]/is',
            fn($m) => sprintf(
                '<a href="%s" rel="nofollow noopener" target="_blank">%s</a>',
                $this->sanitizeUrl($m[1]),
                $m[1]
            ),
            $text
        ) ?? $text;

        // [img] tag
        $text = preg_replace_callback(
            '/\[img\](.*?)\[\/img\]/is',
            fn($m) => sprintf(
                '<img src="%s" alt="User image" loading="lazy" />',
                $this->sanitizeUrl($m[1])
            ),
            $text
        ) ?? $text;

        // [quote] tag
        $text = preg_replace(
            '/\[quote\](.*?)\[\/quote\]/is',
            '<blockquote>$1</blockquote>',
            $text
        ) ?? $text;

        $text = preg_replace(
            '/\[quote=([^\]]+)\](.*?)\[\/quote\]/is',
            '<blockquote><strong>$1 wrote:</strong><br>$2</blockquote>',
            $text
        ) ?? $text;

        // [code] tag
        $text = preg_replace(
            '/\[code\](.*?)\[\/code\]/is',
            '<pre><code>$1</code></pre>',
            $text
        ) ?? $text;

        // [color] tag
        $text = preg_replace_callback(
            '/\[color=([^\]]+)\](.*?)\[\/color\]/is',
            fn($m) => sprintf(
                '<span style="color: %s;">%s</span>',
                $this->sanitizeColor($m[1]),
                $m[2]
            ),
            $text
        ) ?? $text;

        // [size] tag
        $text = preg_replace_callback(
            '/\[size=(\d+)\](.*?)\[\/size\]/is',
            fn($m) => sprintf(
                '<span style="font-size: %dpx;">%s</span>',
                min(max((int)$m[1], 8), 36),
                $m[2]
            ),
            $text
        ) ?? $text;

        // [spoiler] tag
        $text = preg_replace(
            '/\[spoiler\](.*?)\[\/spoiler\]/is',
            '<details class="spoiler"><summary>Spoiler</summary>$1</details>',
            $text
        ) ?? $text;

        $text = preg_replace(
            '/\[spoiler=([^\]]+)\](.*?)\[\/spoiler\]/is',
            '<details class="spoiler"><summary>$1</summary>$2</details>',
            $text
        ) ?? $text;

        // [list] tags
        $text = preg_replace(
            '/\[list\](.*?)\[\/list\]/is',
            '<ul>$1</ul>',
            $text
        ) ?? $text;

        $text = preg_replace(
            '/\[\*\](.*)$/m',
            '<li>$1</li>',
            $text
        ) ?? $text;

        // [artist] tag
        $text = preg_replace(
            '/\[artist\](.*?)\[\/artist\]/is',
            '<a href="/artist.php?name=$1">$1</a>',
            $text
        ) ?? $text;

        // [torrent] tag
        $text = preg_replace(
            '/\[torrent\](\d+)\[\/torrent\]/is',
            '<a href="/torrents.php?id=$1">Torrent #$1</a>',
            $text
        ) ?? $text;

        // [user] tag
        $text = preg_replace(
            '/\[user\](.*?)\[\/user\]/is',
            '<a href="/user.php?action=search&search=$1">$1</a>',
            $text
        ) ?? $text;

        // Auto-link URLs
        $text = preg_replace_callback(
            '/(^|[^"=\]])((https?:\/\/)[^\s<\[\]]+)/i',
            fn($m) => $m[1] . sprintf(
                '<a href="%s" rel="nofollow noopener" target="_blank">%s</a>',
                $m[2],
                $this->truncateUrl($m[2])
            ),
            $text
        ) ?? $text;

        return $text;
    }

    /**
     * Sanitize a URL for use in href/src attributes
     */
    private function sanitizeUrl(string $url): string
    {
        $url = trim($url);

        // Only allow http(s) and relative URLs
        if (!preg_match('/^(https?:\/\/|\/)/i', $url)) {
            return '#';
        }

        return htmlspecialchars($url, ENT_QUOTES);
    }

    /**
     * Sanitize a color value
     */
    private function sanitizeColor(string $color): string
    {
        $color = trim($color);

        // Allow hex colors
        if (preg_match('/^#[0-9a-f]{3,6}$/i', $color)) {
            return $color;
        }

        // Allow named colors
        $namedColors = [
            'red', 'blue', 'green', 'yellow', 'orange', 'purple', 'pink',
            'black', 'white', 'gray', 'grey', 'brown', 'cyan', 'magenta',
        ];

        if (in_array(strtolower($color), $namedColors, true)) {
            return $color;
        }

        return 'inherit';
    }

    /**
     * Truncate a long URL for display
     */
    private function truncateUrl(string $url, int $maxLength = 60): string
    {
        if (strlen($url) <= $maxLength) {
            return htmlspecialchars($url, ENT_QUOTES);
        }

        $start = substr($url, 0, $maxLength - 10);
        $end = substr($url, -7);

        return htmlspecialchars($start . '...' . $end, ENT_QUOTES);
    }

    /**
     * Register default BBCode tag handlers
     */
    private function registerDefaultTags(): void
    {
        // Tags are processed inline in processBbcode()
    }
}
