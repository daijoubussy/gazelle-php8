<?php

declare(strict_types=1);

namespace Gazelle\Core\Config;

/**
 * Configuration Manager
 *
 * Immutable configuration with dot-notation access and environment support.
 */
final readonly class Configuration
{
    /**
     * @param array<string, mixed> $values
     */
    private function __construct(
        private array $values,
        private string $basePath,
        private string $environment
    ) {}

    /**
     * Load configuration from base path
     */
    public static function load(string $basePath): self
    {
        $environment = self::detectEnvironment($basePath);
        $values = self::loadConfigFiles($basePath, $environment);

        return new self($values, $basePath, $environment);
    }

    /**
     * Get a configuration value using dot notation
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->values;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Check if a configuration key exists
     */
    public function has(string $key): bool
    {
        $keys = explode('.', $key);
        $value = $this->values;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return false;
            }
            $value = $value[$segment];
        }

        return true;
    }

    /**
     * Get the base path
     */
    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
    }

    /**
     * Get the current environment
     */
    public function environment(): string
    {
        return $this->environment;
    }

    /**
     * Check if running in production
     */
    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    /**
     * Check if running in development
     */
    public function isDevelopment(): bool
    {
        return $this->environment === 'development';
    }

    /**
     * Check if debug mode is enabled
     */
    public function isDebug(): bool
    {
        return (bool) $this->get('app.debug', false);
    }

    /**
     * Detect environment from .env or environment variable
     */
    private static function detectEnvironment(string $basePath): string
    {
        // Check environment variable first
        $env = getenv('APP_ENV');
        if ($env !== false) {
            return $env;
        }

        // Check .env file
        $envFile = $basePath . DIRECTORY_SEPARATOR . '.env';
        if (file_exists($envFile)) {
            $content = file_get_contents($envFile);
            if (preg_match('/^APP_ENV=(.+)$/m', $content, $matches)) {
                return trim($matches[1]);
            }
        }

        return 'production';
    }

    /**
     * Load all configuration files
     *
     * @return array<string, mixed>
     */
    private static function loadConfigFiles(string $basePath, string $environment): array
    {
        $configPath = $basePath . DIRECTORY_SEPARATOR . 'config';
        $values = [];

        // Load base config files
        if (is_dir($configPath)) {
            foreach (glob($configPath . '/*.php') as $file) {
                $key = basename($file, '.php');
                $values[$key] = require $file;
            }
        }

        // Load environment-specific overrides
        $envConfigPath = $configPath . DIRECTORY_SEPARATOR . $environment;
        if (is_dir($envConfigPath)) {
            foreach (glob($envConfigPath . '/*.php') as $file) {
                $key = basename($file, '.php');
                $envValues = require $file;
                $values[$key] = array_replace_recursive($values[$key] ?? [], $envValues);
            }
        }

        return $values;
    }
}
