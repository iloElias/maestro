<?php

namespace Ilias\Maestro\Database;

use PDO;

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

  public function begin()
  {
    if ($this->inTransaction) {
      throw new \Exception("Transaction already started");
    }

    $this->pdo->beginTransaction();
    $this->inTransaction = true;
  }

  public function commit()
  {
    if (!$this->inTransaction) {
      throw new \Exception("No transaction started");
    }

    $this->pdo->commit();
    $this->inTransaction = false;
  }

  public function rollback()
  {
    if (!$this->inTransaction) {
      throw new \Exception("No transaction started");
    }

    $this->pdo->rollBack();
    $this->inTransaction = false;
  }
}
