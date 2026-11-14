<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Single PostgreSQL connection (PDO, created lazily) and the only place that manages transactions.
 */
final class Database implements TransactionRunnerInterface
{
    private ?\PDO $pdo = null;

    public function __construct(private readonly Config $config)
    {
    }

    public function pdo(): \PDO
    {
        if ($this->pdo === null) {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $this->config->get('DB_HOST', 'db'),
                $this->config->get('DB_PORT', '5432'),
                $this->config->get('DB_NAME', 'apex_steward'),
            );

            $this->pdo = new \PDO(
                $dsn,
                $this->config->require('DB_USER'),
                $this->config->require('DB_PASSWORD'),
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return $this->pdo;
    }

    /**
     * Prepares and executes a statement; every value is bound, never concatenated into SQL.
     *
     * @param array<string, scalar|null>|list<scalar|null> $params
     */
    public function run(string $sql, array $params = []): \PDOStatement
    {
        $statement = $this->pdo()->prepare($sql);

        foreach ($params as $key => $value) {
            $statement->bindValue(
                is_int($key) ? $key + 1 : $key,
                $value,
                match (true) {
                    is_int($value) => \PDO::PARAM_INT,
                    is_bool($value) => \PDO::PARAM_BOOL,
                    $value === null => \PDO::PARAM_NULL,
                    default => \PDO::PARAM_STR,
                }
            );
        }

        $statement->execute();

        return $statement;
    }

    public function transactional(callable $work): mixed
    {
        $pdo = $this->pdo();

        // Already inside a transaction: join it, the outermost caller commits or rolls back.
        if ($pdo->inTransaction()) {
            return $work();
        }

        $pdo->beginTransaction();

        try {
            $result = $work();
            $pdo->commit();

            return $result;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }
}
