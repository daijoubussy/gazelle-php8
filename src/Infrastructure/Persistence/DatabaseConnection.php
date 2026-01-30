<?php

declare(strict_types=1);

namespace Gazelle\Infrastructure\Persistence;

use Gazelle\Core\Config\Configuration;

/**
 * Database Connection
 *
 * Manages PDO connection with proper configuration and error handling.
 */
final class DatabaseConnection
{
    private ?\PDO $pdo = null;

    public function __construct(
        private readonly Configuration $config
    ) {}

    /**
     * Get the PDO connection
     */
    public function connection(): \PDO
    {
        if ($this->pdo === null) {
            $this->pdo = $this->createConnection();
        }

        return $this->pdo;
    }

    /**
     * Execute a query with parameters
     *
     * @param array<mixed> $params
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $statement = $this->connection()->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    /**
     * Execute a query and return all results
     *
     * @param array<mixed> $params
     * @return array<array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Execute a query and return the first row
     *
     * @param array<mixed> $params
     * @return array<string, mixed>|null
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch(\PDO::FETCH_ASSOC);

        return $result !== false ? $result : null;
    }

    /**
     * Execute a query and return a single column value
     *
     * @param array<mixed> $params
     */
    public function fetchColumn(string $sql, array $params = [], int $column = 0): mixed
    {
        return $this->query($sql, $params)->fetchColumn($column);
    }

    /**
     * Execute an insert and return the last insert ID
     *
     * @param array<mixed> $params
     */
    public function insert(string $sql, array $params = []): int
    {
        $this->query($sql, $params);

        return (int) $this->connection()->lastInsertId();
    }

    /**
     * Execute an update/delete and return affected rows
     *
     * @param array<mixed> $params
     */
    public function execute(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction(): void
    {
        $this->connection()->beginTransaction();
    }

    /**
     * Commit the current transaction
     */
    public function commit(): void
    {
        $this->connection()->commit();
    }

    /**
     * Rollback the current transaction
     */
    public function rollback(): void
    {
        $this->connection()->rollBack();
    }

    /**
     * Execute a callback within a transaction
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback();
            $this->commit();

            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Close the connection
     */
    public function close(): void
    {
        $this->pdo = null;
    }

    /**
     * Create a new PDO connection
     */
    private function createConnection(): \PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $this->config->get('database.host', 'localhost'),
            $this->config->get('database.port', 3306),
            $this->config->get('database.database', 'gazelle')
        );

        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_STRINGIFY_FETCHES => false,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        return new \PDO(
            $dsn,
            $this->config->get('database.username', 'root'),
            $this->config->get('database.password', ''),
            $options
        );
    }
}
