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
   * @return void
   */
  public function begin()
  {
    if ($this->inTransaction) {
      return;
    }

    $this->pdo->beginTransaction();
    $this->inTransaction = true;
  }

  /**
   * Commits the current database transaction.
   * @return void
   */
  public function commit()
  {
    if (!$this->inTransaction) {
      return;
    }

    $this->pdo->commit();
    $this->inTransaction = false;
  }

  /**
   * Rolls back the current database transaction.
   * @return void
   */
  public function rollback()
  {
    if (!$this->inTransaction) {
      return;
    }

    $this->pdo->rollBack();
    $this->inTransaction = false;
  }
}
