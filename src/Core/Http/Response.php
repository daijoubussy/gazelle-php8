<?php

declare(strict_types=1);

namespace Gazelle\Core\Http;

/**
 * HTTP Response
 *
 * Immutable value object representing an HTTP response.
 */
final readonly class Response
{
    /**
     * @param array<string, string> $headers
     */
    private function __construct(
        private string $content,
        private int $status,
        private array $headers
    ) {}

    /**
     * Create a new response
     *
     * @param array<string, string> $headers
     */
    public static function make(
        string $content = '',
        int $status = 200,
        array $headers = []
    ): self {
        return new self($content, $status, $headers);
    }

    /**
     * Create a JSON response
     *
     * @param array<string, string> $headers
     */
    public static function json(
        mixed $data,
        int $status = 200,
        array $headers = []
    ): self {
        $headers['Content-Type'] = 'application/json';

        return new self(
            json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            $status,
            $headers
        );
    }

    /**
     * Create a redirect response
     */
    public static function redirect(string $url, int $status = 302): self
    {
        return new self('', $status, ['Location' => $url]);
    }

    /**
     * Create a 404 response
     */
    public static function notFound(string $message = 'Not Found'): self
    {
        return new self($message, 404, []);
    }

    /**
     * Create a 401 response
     */
    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new self($message, 401, []);
    }

    /**
     * Create a 403 response
     */
    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new self($message, 403, []);
    }

    /**
     * Create a 500 response
     */
    public static function serverError(string $message = 'Internal Server Error'): self
    {
        return new self($message, 500, []);
    }

    /**
     * Create a no-content response
     */
    public static function noContent(): self
    {
        return new self('', 204, []);
    }

    public function content(): string
    {
        return $this->content;
    }

    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function header(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Create a new response with a different status
     */
    public function withStatus(int $status): self
    {
        return new self($this->content, $status, $this->headers);
    }

    /**
     * Create a new response with an additional header
     */
    public function withHeader(string $name, string $value): self
    {
        $headers = $this->headers;
        $headers[$name] = $value;

        return new self($this->content, $this->status, $headers);
    }

    /**
     * Create a new response with a cookie
     */
    public function withCookie(
        string $name,
        string $value,
        int $expires = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = true,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ): self {
        $cookie = "{$name}=" . urlencode($value);

        if ($expires > 0) {
            $cookie .= '; Expires=' . gmdate('D, d M Y H:i:s T', $expires);
        }

        $cookie .= "; Path={$path}";

        if ($domain !== '') {
            $cookie .= "; Domain={$domain}";
        }

        if ($secure) {
            $cookie .= '; Secure';
        }

        if ($httpOnly) {
            $cookie .= '; HttpOnly';
        }

        $cookie .= "; SameSite={$sameSite}";

        $headers = $this->headers;
        $headers['Set-Cookie'] = $cookie;

        return new self($this->content, $this->status, $headers);
    }

    /**
     * Send the response to the client
     */
    public function send(): void
    {
        // Send status
        http_response_code($this->status);

        // Send headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // Send content
        echo $this->content;
    }
}
