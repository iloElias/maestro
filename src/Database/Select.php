<?php

namespace Ilias\Maestro\Database;

use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Interface\Sql;

class Select extends Sql
{
  private string $from;
  private array $columns = [];
  private array $joins = [];
  private array $where = [];
  private array $order = [];
  private array $parameters = [];

  public function select(...$columns): Select
  {
    $this->columns = $columns;
    return $this;
  }

  public function from(string|Table $table): Select
  {
    $this->from = $table;
    return $this;
  }

  public function join(string $table, string $condition, string $type = 'INNER'): Select
  {
    $this->joins[] = strtoupper($type) . " JOIN " . $table . " ON " . $condition;
    return $this;
  }

  public function where(array $conditions)
  {
    foreach ($conditions as $column => $value) {
      $sanitizedColumn = str_replace('.', '_', $column);
      $paramName = ":where_$sanitizedColumn";
      $this->where[] = "{$column} = {$paramName}";
      $this->parameters[$paramName] = $value;
    }
    return $this;
  }

  public function in(array $conditions): Select
  {
    foreach ($conditions as $column => $value) {
      $inParams = array_map(function ($v, $k) use ($column) {
        $paramName = ":in_{$column}_{$k}";
        $this->parameters[$paramName] = $v;
        return $paramName;
      }, $value, array_keys($value));
      $inList = implode(",", $inParams);
      $this->where[] = "{$column} IN({$inList})";
    }
    return $this;
  }

  public function order(string $column, string $direction = 'ASC'): Select
  {
    $this->order[] = "$column $direction";
    return $this;
  }

  public function getSql(): string
  {
    $columns = implode(", ", $this->columns);
    $joins = implode(" ", $this->joins);
    $whereClause = implode(" AND ", $this->where);
    $orderClause = implode(", ", $this->order);

    $sql = "SELECT $columns FROM {$this->from}";
    if ($joins) {
      $sql .= " " . $joins;
    }
    if ($whereClause) {
      $sql .= " WHERE " . $whereClause;
    }
    if ($orderClause) {
      $sql .= " ORDER BY " . $orderClause;
    }

    return $sql;
  }

  public function getParameters(): array
  {
    return $this->parameters;
  }
}
