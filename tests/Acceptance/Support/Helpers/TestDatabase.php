<?php

declare(strict_types=1);

namespace Gazelle\Tests\Acceptance\Support\Helpers;

use PDO;
use PDOException;

/**
 * Test Database helper for deterministic database testing
 *
 * Provides transaction-based isolation so each test scenario starts
 * with a known state and all changes are rolled back after the test.
 */
final class TestDatabase
{
    private ?PDO $connection = null;
    private bool $inTransaction = false;
    private int $transactionDepth = 0;

    /** @var array<string> Tables to preserve (not truncated) */
    private array $preservedTables = [
        'permissions',
        'stylesheets',
        'wiki_articles', // System wiki pages
    ];

    public function __construct()
    {
        // Connection will be lazy-loaded
    }

    /**
     * Get database connection
     */
    public function connection(): PDO
    {
        if ($this->connection === null) {
            $this->connection = $this->createConnection();
        }
        return $this->connection;
    }

    /**
     * Begin a transaction for test isolation
     */
    public function beginTransaction(): void
    {
        if ($this->transactionDepth === 0) {
            $this->connection()->beginTransaction();
            $this->inTransaction = true;
        }
        $this->transactionDepth++;
    }

    /**
     * Rollback transaction (undoes all changes from test)
     */
    public function rollback(): void
    {
        if ($this->transactionDepth > 0) {
            $this->transactionDepth--;
        }

        if ($this->transactionDepth === 0 && $this->inTransaction) {
            $this->connection()->rollBack();
            $this->inTransaction = false;
        }
    }

    /**
     * Commit transaction (use sparingly in tests)
     */
    public function commit(): void
    {
        if ($this->transactionDepth > 0) {
            $this->transactionDepth--;
        }

        if ($this->transactionDepth === 0 && $this->inTransaction) {
            $this->connection()->commit();
            $this->inTransaction = false;
        }
    }

    /**
     * Truncate all tables (except preserved ones)
     */
    public function truncateAll(): void
    {
        $tables = $this->getAllTables();

        $this->connection()->exec('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($tables as $table) {
            if (!in_array($table, $this->preservedTables, true)) {
                $this->connection()->exec("TRUNCATE TABLE `{$table}`");
            }
        }

        $this->connection()->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Truncate specific tables
     *
     * @param array<string> $tables
     */
    public function truncate(array $tables): void
    {
        $this->connection()->exec('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($tables as $table) {
            $this->connection()->exec("TRUNCATE TABLE `{$table}`");
        }

        $this->connection()->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Insert a record and return the ID
     *
     * @param array<string, mixed> $data
     */
    public function insert(string $table, array $data): int
    {
        $columns = implode('`, `', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO `{$table}` (`{$columns}`) VALUES ({$placeholders})";
        $stmt = $this->connection()->prepare($sql);
        $stmt->execute(array_values($data));

        return (int) $this->connection()->lastInsertId();
    }

    /**
     * Update records
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $where
     */
    public function update(string $table, array $data, array $where): int
    {
        $setParts = [];
        $values = [];

        foreach ($data as $column => $value) {
            $setParts[] = "`{$column}` = ?";
            $values[] = $value;
        }

        $whereParts = [];
        foreach ($where as $column => $value) {
            $whereParts[] = "`{$column}` = ?";
            $values[] = $value;
        }

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE %s',
            $table,
            implode(', ', $setParts),
            implode(' AND ', $whereParts)
        );

        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($values);

        return $stmt->rowCount();
    }

    /**
     * Delete records
     *
     * @param array<string, mixed> $where
     */
    public function delete(string $table, array $where): int
    {
        $whereParts = [];
        $values = [];

        foreach ($where as $column => $value) {
            $whereParts[] = "`{$column}` = ?";
            $values[] = $value;
        }

        $sql = sprintf(
            'DELETE FROM `%s` WHERE %s',
            $table,
            implode(' AND ', $whereParts)
        );

        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($values);

        return $stmt->rowCount();
    }

    /**
     * Find a single record
     *
     * @param array<string, mixed> $where
     * @return array<string, mixed>|null
     */
    public function find(string $table, array $where): ?array
    {
        $whereParts = [];
        $values = [];

        foreach ($where as $column => $value) {
            $whereParts[] = "`{$column}` = ?";
            $values[] = $value;
        }

        $sql = sprintf(
            'SELECT * FROM `%s` WHERE %s LIMIT 1',
            $table,
            implode(' AND ', $whereParts)
        );

        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($values);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }

    /**
     * Find all records matching criteria
     *
     * @param array<string, mixed> $where
     * @return array<array<string, mixed>>
     */
    public function findAll(string $table, array $where = []): array
    {
        if (empty($where)) {
            $sql = "SELECT * FROM `{$table}`";
            $stmt = $this->connection()->query($sql);
        } else {
            $whereParts = [];
            $values = [];

            foreach ($where as $column => $value) {
                $whereParts[] = "`{$column}` = ?";
                $values[] = $value;
            }

            $sql = sprintf(
                'SELECT * FROM `%s` WHERE %s',
                $table,
                implode(' AND ', $whereParts)
            );

            $stmt = $this->connection()->prepare($sql);
            $stmt->execute($values);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count records
     *
     * @param array<string, mixed> $where
     */
    public function count(string $table, array $where = []): int
    {
        if (empty($where)) {
            $sql = "SELECT COUNT(*) FROM `{$table}`";
            $stmt = $this->connection()->query($sql);
        } else {
            $whereParts = [];
            $values = [];

            foreach ($where as $column => $value) {
                $whereParts[] = "`{$column}` = ?";
                $values[] = $value;
            }

            $sql = sprintf(
                'SELECT COUNT(*) FROM `%s` WHERE %s',
                $table,
                implode(' AND ', $whereParts)
            );

            $stmt = $this->connection()->prepare($sql);
            $stmt->execute($values);
        }

        return (int) $stmt->fetchColumn();
    }

    /**
     * Execute raw SQL (use sparingly)
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Query raw SQL and return results
     *
     * @return array<array<string, mixed>>
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if table exists
     */
    public function tableExists(string $table): bool
    {
        $sql = "SHOW TABLES LIKE ?";
        $stmt = $this->connection()->prepare($sql);
        $stmt->execute([$table]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get all table names in database
     *
     * @return array<string>
     */
    private function getAllTables(): array
    {
        $stmt = $this->connection()->query('SHOW TABLES');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Create database connection
     */
    private function createConnection(): PDO
    {
        // Use test database configuration
        $host = getenv('TEST_DB_HOST') ?: 'localhost';
        $port = getenv('TEST_DB_PORT') ?: '3306';
        $name = getenv('TEST_DB_NAME') ?: 'gazelle_test';
        $user = getenv('TEST_DB_USER') ?: 'gazelle_test';
        $pass = getenv('TEST_DB_PASS') ?: 'gazelle_test';

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            // If test database doesn't exist, use SQLite in-memory for basic tests
            $pdo = new PDO('sqlite::memory:', null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }

        return $pdo;
    }
}
