<?php

declare(strict_types=1);

namespace Gazelle\Application\Auth\Services;

/**
 * Cookie Service
 *
 * Application service for secure cookie management.
 * Replaces legacy Cookies class.
 */
final readonly class CookieService
{
    private const DEFAULT_EXPIRY = 2592000; // 30 days

    public function __construct(
        private string $domain,
        private bool $secure = true,
        private bool $httpOnly = true,
        private string $sameSite = 'Lax'
    ) {}

    /**
     * Set a cookie
     */
    public function set(
        string $name,
        string $value,
        int $expiry = self::DEFAULT_EXPIRY,
        string $path = '/'
    ): void {
        $options = [
            'expires' => time() + $expiry,
            'path' => $path,
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httponly' => $this->httpOnly,
            'samesite' => $this->sameSite,
        ];

        setcookie($name, $value, $options);
        $_COOKIE[$name] = $value;
    }

    /**
     * Get a cookie value
     */
    public function get(string $name, ?string $default = null): ?string
    {
        return $_COOKIE[$name] ?? $default;
    }

    /**
     * Check if a cookie exists
     */
    public function has(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Delete a cookie
     */
    public function delete(string $name, string $path = '/'): void
    {
        $options = [
            'expires' => time() - 3600,
            'path' => $path,
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httponly' => $this->httpOnly,
            'samesite' => $this->sameSite,
        ];

        setcookie($name, '', $options);
        unset($_COOKIE[$name]);
    }

    /**
     * Set a secure session cookie
     */
    public function setSession(string $name, string $value, string $path = '/'): void
    {
        $options = [
            'expires' => 0, // Session cookie
            'path' => $path,
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httponly' => $this->httpOnly,
            'samesite' => $this->sameSite,
        ];

        setcookie($name, $value, $options);
        $_COOKIE[$name] = $value;
    }

    /**
     * Set an encrypted cookie
     */
    public function setEncrypted(
        string $name,
        string $value,
        string $key,
        int $expiry = self::DEFAULT_EXPIRY,
        string $path = '/'
    ): void {
        $encrypted = $this->encrypt($value, $key);
        $this->set($name, $encrypted, $expiry, $path);
    }

    /**
     * Get an encrypted cookie value
     */
    public function getEncrypted(string $name, string $key): ?string
    {
        $encrypted = $this->get($name);

        if ($encrypted === null) {
            return null;
        }

        return $this->decrypt($encrypted, $key);
    }

    /**
     * Encrypt a value for storage in a cookie
     */
    private function encrypt(string $value, string $key): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt(
            $value,
            'AES-256-CBC',
            hash('sha256', $key, true),
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed');
        }

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt a value from a cookie
     */
    private function decrypt(string $encrypted, string $key): ?string
    {
        $data = base64_decode($encrypted, true);

        if ($data === false || strlen($data) < 17) {
            return null;
        }

        $iv = substr($data, 0, 16);
        $ciphertext = substr($data, 16);

        $decrypted = openssl_decrypt(
            $ciphertext,
            'AES-256-CBC',
            hash('sha256', $key, true),
            OPENSSL_RAW_DATA,
            $iv
        );

        return $decrypted !== false ? $decrypted : null;
    }

    /**
     * Get all cookies
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        return $_COOKIE;
    }

    /**
     * Clear all cookies
     */
    public function clear(): void
    {
        foreach (array_keys($_COOKIE) as $name) {
            $this->delete($name);
        }
    }
}
