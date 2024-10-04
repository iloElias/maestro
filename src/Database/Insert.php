<?php

namespace Ilias\Maestro\Database;

use Ilias\Maestro\Abstract\Query;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Utils\Utils;

class Insert extends Query
{
  private $table = '';
  private $columns = [];
  private $values = [];
  private $returning = [];

  public function into(string $table): Insert
  {
    $this->table = $this->validateTableName($table);
    return $this;
  }

  private function registerValue($column, $value)
  {
    $column = Utils::sanitizeForPostgres($column);
    $this->columns[] = $column;
    $paramName = ":$column";
    $this->values[] = $paramName;
    $this->parameters[$paramName] = $value;
  }

  public function values(Table|array $data): Insert
  {
    if (is_object($data)) {
      foreach ((array)$data as $column => $value) {
        $this->registerValue($column, $value);
      }
    }
    if (is_array($data)) {
      foreach ($data as $column => $value) {
        $this->registerValue($column, $value);
      }
    }
    return $this;
  }

  /**
   * @deprecated This method does not make anything when used. Since you're building a insert query
   *
   * @param array $conditions An associative array of conditions for the WHERE clause.
   * @return Insert Returns the current Insert instance.
   */
  public function where(array $conditions): static
  {
    return $this;
  }

  /**
   * @deprecated This method does not make anything when used. Since you're building a insert query
   *
   * @param array $conditions An associative array of conditions for the WHERE clause.
   * @return Insert Returns the current Insert instance.
   */
  public function in(array $conditions): static
  {
    return $this;
  }

  public function returning(array $columns): Insert
  {
    foreach ($columns as $column) {
      if (!in_array($column, $this->returning)) {
        $this->returning[] = $column;
      }
    }
    return $this;
  }

  public function getSql(): string
  {
    $sql = [];

    if (!empty($this->table) && !empty($this->columns) && !empty($this->values)) {
      $sql[] = "INSERT INTO " . $this->table;
      $sql[] = "(" . implode(", ", $this->columns) . ")";
      $sql[] = "VALUES (" . implode(", ", $this->values) . ")";
    }

    if (!empty($this->returning)) {
      $sql[] = "RETURNING " . implode(", ", $this->returning);
    }

    return implode(" ", $sql);
  }
}
