<?php

namespace Ilias\Maestro\Database;

use Ilias\Maestro\Abstract\Query;
use Ilias\Maestro\Utils\Utils;

class Update extends Query
{
  private $table;
  private $set = [];

  public function table(string $table): Update
  {
    $this->table = $this->validateTableName($table);
    return $this;
  }

  public function set(string $column, $value): Update
  {
    $column = Utils::sanitizeForPostgres($column);
    $paramName = ":$column";
    $this->set[$column] = $paramName;
    $this->parameters[$paramName] = $value;
    return $this;
  }

  public function getSql(): string
  {
    $setClause = implode(", ", array_map(fn($k, $v) => "$k = $v", array_keys($this->set), $this->set));
    $whereClause = implode(" AND ", $this->where);

    return "UPDATE {$this->table} SET $setClause" . ($whereClause ? " WHERE $whereClause" : "");
    ;
  }
}
