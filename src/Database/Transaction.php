<?php

namespace Ilias\Maestro\Database;

use PDO;

/**
 * This class provides methods to manage database transactions.
 */
class Transaction
{
  private PDO $pdo;
  private bool $inTransaction = false;

  public function __construct(?PDO $pdo = null)
  {
    if (empty($pdo)) {
      $pdo = PDOConnection::get();
    }
    $this->pdo = $pdo;
  }

  /**
   * Begins a new database transaction.
   * @throws \Exception if a transaction is already started.
   * @return void
   */
  public function begin()
  {
    if ($this->inTransaction) {
      throw new \Exception("Transaction already started");
    }

    $this->pdo->beginTransaction();
    $this->inTransaction = true;
  }

  /**
   * Commits the current database transaction.
   * @throws \Exception if no transaction is started.
   * @return void
   */
  public function commit()
  {
    if (!$this->inTransaction) {
      throw new \Exception("No transaction started");
    }

    $this->pdo->commit();
    $this->inTransaction = false;
  }

  /**
   * Rolls back the current database transaction.
   * @throws \Exception if no transaction is started.
   * @return void
   */
  public function rollback()
  {
    if (!$this->inTransaction) {
      throw new \Exception("No transaction started");
    }

    $this->pdo->rollBack();
    $this->inTransaction = false;
  }
}
