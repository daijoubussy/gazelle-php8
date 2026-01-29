#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * PHP 8.5 Migration Script for Gazelle
 *
 * This script updates PHP files to be compatible with PHP 8.5:
 * - Converts short open tags (<?) to full open tags (<?php)
 * - Replaces deprecated apc_* functions with apcu_* equivalents
 * - Fixes deprecated each() function calls
 */

class PHP85Migrator
{
    private string $basePath;
    private int $filesProcessed = 0;
    private int $filesModified = 0;
    private array $errors = [];

    public function __construct(string $basePath)
    {
        $this->basePath = realpath($basePath) ?: $basePath;
    }

    public function run(): void
    {
        echo "PHP 8.5 Migration Script\n";
        echo "========================\n\n";
        echo "Base path: {$this->basePath}\n\n";

        $this->processDirectory($this->basePath);

        echo "\nMigration Complete!\n";
        echo "-------------------\n";
        echo "Files processed: {$this->filesProcessed}\n";
        echo "Files modified: {$this->filesModified}\n";

        if (!empty($this->errors)) {
            echo "\nErrors encountered:\n";
            foreach ($this->errors as $error) {
                echo "  - $error\n";
            }
        }
    }

    private function processDirectory(string $directory): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $path = $file->getPathname();

            // Skip vendor, node_modules, .git directories
            if (preg_match('#/(vendor|node_modules|\.git)/#', $path)) {
                continue;
            }

            // Skip this migration script and non-PHP files
            if ($path === __FILE__) {
                continue;
            }

            // Only process PHP files
            $extension = $file->getExtension();
            if ($extension !== 'php' && !str_ends_with($path, '.class.php')) {
                continue;
            }

            $this->processFile($path);
        }
    }

    private function processFile(string $filePath): void
    {
        $this->filesProcessed++;

        try {
            $content = file_get_contents($filePath);
            if ($content === false) {
                $this->errors[] = "Could not read: $filePath";
                return;
            }

            $originalContent = $content;

            // Apply transformations
            $content = $this->fixShortOpenTags($content);
            $content = $this->fixApcFunctions($content);
            $content = $this->fixEachFunction($content);
            $content = $this->fixArraySyntax($content);

            // Only write if changes were made
            if ($content !== $originalContent) {
                if (file_put_contents($filePath, $content) === false) {
                    $this->errors[] = "Could not write: $filePath";
                    return;
                }
                $this->filesModified++;
                echo "Modified: $filePath\n";
            }
        } catch (Throwable $e) {
            $this->errors[] = "$filePath: " . $e->getMessage();
        }
    }

    /**
     * Convert short open tags (<?) to full open tags (<?php)
     * Preserves <?= short echo tags which are valid
     */
    private function fixShortOpenTags(string $content): string
    {
        // Replace <? followed by whitespace or newline (but not <?php or <?=)
        $content = preg_replace(
            '/^<\?(?!php|=)(\s)/m',
            '<?php$1',
            $content
        );

        // Handle <? at very start of file followed immediately by newline
        if (str_starts_with($content, "<?\n")) {
            $content = "<?php\n" . substr($content, 3);
        }

        // Handle <? followed directly by content (no space)
        $content = preg_replace(
            '/<\?(?!php|=)([a-zA-Z_])/',
            '<?php $1',
            $content
        );

        return $content;
    }

    /**
     * Replace deprecated APC functions with APCu equivalents
     */
    private function fixApcFunctions(string $content): string
    {
        $replacements = [
            'apc_exists' => 'apcu_exists',
            'apc_fetch' => 'apcu_fetch',
            'apc_store' => 'apcu_store',
            'apc_delete' => 'apcu_delete',
            'apc_clear_cache' => 'apcu_clear_cache',
            'apc_add' => 'apcu_add',
            'apc_inc' => 'apcu_inc',
            'apc_dec' => 'apcu_dec',
            'apc_cas' => 'apcu_cas',
        ];

        foreach ($replacements as $old => $new) {
            // Use word boundaries to avoid replacing in comments or strings inappropriately
            $content = preg_replace('/\b' . preg_quote($old, '/') . '\b/', $new, $content);
        }

        return $content;
    }

    /**
     * Replace deprecated each() function with foreach or current()/key()
     */
    private function fixEachFunction(string $content): string
    {
        // Common pattern: list($key, $value) = each($array)
        // Replace with: foreach logic or array_key_first/current
        $content = preg_replace(
            '/list\s*\(\s*\$(\w+)\s*,\s*\$(\w+)\s*\)\s*=\s*each\s*\(\s*\$(\w+)\s*\)/',
            '[$$$1, $$$2] = [key($$$3), current($$$3)]; next($$$3)',
            $content
        );

        // Pattern: list($key, $data) = each($Var) in context
        $content = preg_replace(
            '/list\s*\(\s*\$(\w+)\s*,\s*\$(\w+)\s*\)\s*=\s*each\s*\(\s*\$(\w+)\s*\);/',
            'if (($$$1 = key($$$3)) !== null) { $$$2 = current($$$3); next($$$3); }',
            $content
        );

        return $content;
    }

    /**
     * Convert old array() syntax to [] where appropriate (optional cleanup)
     */
    private function fixArraySyntax(string $content): string
    {
        // Skip this transformation as it can break complex nested arrays
        // This is left as a separate pass if needed
        return $content;
    }
}

// Run the migration
if (php_sapi_name() === 'cli') {
    $basePath = $argv[1] ?? dirname(__DIR__);
    $migrator = new PHP85Migrator($basePath);
    $migrator->run();
}
