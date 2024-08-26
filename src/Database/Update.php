<?php

namespace Ilias\Maestro\Database;

use Ilias\Maestro\Interface\Sql;

class Update extends Sql
{
  private $table;
  private $set = [];
  private $where = [];
  private $parameters = [];

  public function table(string $table): Update
  {
    $this->table = $table;
    return $this;
  }

  public function set(string $column, $value): Update
  {
    $paramName = ":$column";
    $this->set[$column] = $paramName;
    $this->parameters[$paramName] = $value;
    return $this;
  }

  public function where(array $conditions): Update
  {
    foreach ($conditions as $column => $value) {
      $paramName = ":where_$column";
      $this->where[] = "{$column} = {$paramName}";
      $this->parameters[$paramName] = $value;
    }
    return $this;
  }

  public function in(array $conditions): Update
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
    $setClause = implode(", ", array_map(fn ($k, $v) => "$k = $v", array_keys($this->set), $this->set));
    $whereClause = implode(" AND ", $this->where);

    return "UPDATE {$this->table} SET $setClause" . ($whereClause ? " WHERE $whereClause" : "");;
  }

  public function getParameters(): array
  {
    return $this->parameters;
  }
}
