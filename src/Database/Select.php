<?php

namespace Ilias\Maestro\Database;

use Ilias\Maestro\Interface\Sql;

class Select implements Sql
{
  private $select = [];
  private $from = '';
  private $joins = [];
  private $wheres = [];
  private $orderBys = [];
  private $limit = '';
  private $parameters = [];

  public function select(...$columns)
  {
    $this->select = array_merge($this->select, $columns);
    return $this;
  }

  public function from(string $table)
  {
    try {
      $this->from = call_user_func("{$table}::getTableSchemaAddress");
    } catch (\Throwable) {
      $this->from = $table;
    }

    return $this;
  }

  public function join(string $type, array $tableAndAlias, string $condition)
  {
    [$table] = $tableAndAlias;
    [$alias] = array_flip($tableAndAlias);
    try {
      $table = call_user_func("{$table}::getTableSchemaAddress");
    } catch (\Throwable) {
    }

    $this->joins[] = strtoupper($type) . " JOIN {$table} AS {$alias} ON {$condition}";
    return $this;
  }

  public function where(string $condition, $parameters = [])
  {
    $this->wheres[] = $condition;
    $this->parameters = array_merge($this->parameters, $parameters);
    return $this;
  }

  public function orderBy($column, $direction = 'ASC')
  {
    $this->orderBys[] = "$column $direction";
    return $this;
  }

  public function limit($limit)
  {
    $this->limit = "LIMIT $limit";
    return $this;
  }

  public function getSql(): string
  {
    $sql = [];

    if (!empty($this->select)) {
      $sql[] = "SELECT " . implode(", ", $this->select);
    } else {
      $sql[] = "SELECT *";
    }

    if (!empty($this->from)) {
      $sql[] = "FROM " . $this->from;
    }

    if (!empty($this->joins)) {
      $sql[] = implode(" ", $this->joins);
    }

    if (!empty($this->wheres)) {
      $sql[] = "WHERE " . implode(" AND ", $this->wheres);
    }

    if (!empty($this->orderBys)) {
      $sql[] = "ORDER BY " . implode(", ", $this->orderBys);
    }

    if (!empty($this->limit)) {
      $sql[] = $this->limit;
    }

    return implode(" ", $sql);
  }

  public function getParameters(): array
  {
    return $this->parameters;
  }
}
