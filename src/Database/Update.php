<?php

namespace Ilias\Maestro\Database;

use Ilias\Maestro\Interface\Sql;

class Update implements Sql
{
  private $table = '';
  private $sets = [];
  private $wheres = [];
  private $parameters = [];

  public function table(string $table)
  {
    try {
      $this->table = call_user_func("{$table}::getTableSchemaAddress");
    } catch (\Throwable) {
      $this->table = $table;
    }

    return $this;
  }

  public function set(string $column, $value)
  {
    $this->sets[$column] = $value;
    $this->parameters[$column] = $value;
    return $this;
  }

  public function where(string $condition, array $parameters = [])
  {
    $this->wheres[] = $condition;
    $this->parameters = array_merge($this->parameters, $parameters);
    return $this;
  }

  public function getSql(): string
  {
    $sql = [];

    if (!empty($this->table)) {
      $sql[] = "UPDATE " . $this->table;
    }

    if (!empty($this->sets)) {
      $setClauses = [];
      foreach ($this->sets as $column => $value) {
        $setClauses[] = "$column = :$column";
      }
      $sql[] = "SET " . implode(", ", $setClauses);
    }

    if (!empty($this->wheres)) {
      $sql[] = "WHERE " . implode(" AND ", $this->wheres);
    }

    return implode(" ", $sql);
  }

  public function getParameters(): array
  {
    return $this->parameters;
  }
}
