<?php

declare(strict_types=1);

namespace Gazelle\Core\Http;

/**
 * HTTP Request
 *
 * Immutable value object representing an HTTP request.
 * PSR-7 inspired but simplified for our use case.
 */
final readonly class Request
{
    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $files
     * @param array<string, mixed> $server
     * @param array<string, mixed> $attributes
     */
    private function __construct(
        private string $method,
        private string $uri,
        private string $path,
        private array $headers,
        private array $query,
        private array $body,
        private array $cookies,
        private array $files,
        private array $server,
        private array $attributes = []
    ) {}

    /**
     * Create request from PHP globals
     */
    public static function capture(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = str_replace('_', '-', substr($key, 5));
                $headers[strtolower($headerName)] = $value;
            }
        }

        // Parse JSON body if applicable
        $body = $_POST;
        $contentType = $headers['content-type'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $rawBody = file_get_contents('php://input');
            if ($rawBody !== false) {
                $jsonBody = json_decode($rawBody, true);
                if (is_array($jsonBody)) {
                    $body = $jsonBody;
                }
            }
        }

        return new self(
            method: strtoupper($method),
            uri: $uri,
            path: $path,
            headers: $headers,
            query: $_GET,
            body: $body,
            cookies: $_COOKIE,
            files: $_FILES,
            server: $_SERVER
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function allQuery(): array
    {
        return $this->query;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function ip(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR']
            ?? $this->server['HTTP_CLIENT_IP']
            ?? $this->server['REMOTE_ADDR']
            ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $this->header('user-agent', '');
    }

    public function isAjax(): bool
    {
        return $this->header('x-requested-with') === 'XMLHttpRequest';
    }

    public function isJson(): bool
    {
        return str_contains($this->header('content-type', ''), 'application/json');
    }

    public function expectsJson(): bool
    {
        return str_contains($this->header('accept', ''), 'application/json');
    }

    public function bearerToken(): ?string
    {
        $auth = $this->header('authorization', '');
        if (str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }
        return null;
    }

    /**
     * Get a route attribute
     */
    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Create a new request with added attributes
     *
     * @param array<string, mixed> $attributes
     */
    public function withAttributes(array $attributes): self
    {
        return new self(
            method: $this->method,
            uri: $this->uri,
            path: $this->path,
            headers: $this->headers,
            query: $this->query,
            body: $this->body,
            cookies: $this->cookies,
            files: $this->files,
            server: $this->server,
            attributes: array_merge($this->attributes, $attributes)
        );
    }
}
