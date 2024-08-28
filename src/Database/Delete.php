<?php

namespace Ilias\Maestro\Database;

use Ilias\Maestro\Abstract\Sql;

class Delete extends Sql
{
  private $table;
  private $where = [];
  private $parameters = [];

  public function from(string $table): Delete
  {
    $this->table = $table;
    return $this;
  }

  public function where(array $conditions): Delete
  {
    foreach ($conditions as $column => $value) {
      $paramName = ":where_{$column}";
      $this->where[] = "{$column} = {$paramName}";
      $this->parameters[$paramName] = $value;
    }
    return $this;
  }

  public function in(array $conditions): Delete
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

  public function getSql(): string
  {
    $whereClause = implode(" AND ", $this->where);

    $sql = "DELETE FROM {$this->table}";
    if ($whereClause) {
        $sql .= " WHERE $whereClause";
    }
    return $sql;
  }

  public function getParameters(): array
  {
    return $this->parameters;
  }
}
