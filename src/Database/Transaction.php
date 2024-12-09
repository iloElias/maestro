<?php

namespace Ilias\Maestro\Database;

use PDO;

/**
 * This class provides methods to manage database transactions.
 */
class Transaction
{
    private static PDO $pdo;
    private static bool $inTransaction = false;

    public function __construct(?PDO $pdo = null)
    {
        if (empty($pdo)) {
            $pdo = Connection::get();
        }
        self::$pdo = $pdo;
    }

    /**
     * Begins a new database transaction.
     */
    public static function begin(): void
    {
        if (self::$inTransaction) {
            return;
        }

        self::$pdo->beginTransaction();
        self::$inTransaction = true;
    }

    /**
     * Commits the current database transaction.
     */
    public static function commit(): void
    {
        if (!self::$inTransaction) {
            return;
        }

        self::$pdo->commit();
        self::$inTransaction = false;
    }

    /**
     * Rolls back the current database transaction.
     */
    public static function rollback()
    {
        if (!self::$inTransaction) {
            return;
        }

        self::$pdo->rollBack();
        self::$inTransaction = false;
    }
}
