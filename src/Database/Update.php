<?php

namespace Ilias\Maestro\Database;

use Ilias\Maestro\Abstract\Bindable;

class Update extends Bindable
{
  private $table;
  private $set = [];
  private $where = [];

  public function table(string $table): Update
  {
    $this->table = $this->validateTableName($table);
    return $this;
  }

  public function set(string $column, $value): Update
  {
    $paramName = ":$column";
    $this->set[$column] = $paramName;
    $this->parameters[$paramName] = $value;
    return $this;
  }

  /**
   * Adds WHERE conditions to the SQL query.
   * This method accepts an associative array of conditions where the key is the column name and the value is the condition value.
   *
   * @param array $conditions An associative array of conditions for the WHERE clause.
   * @return $this Returns the current instance for method chaining.
   */
  public function where(array $conditions): Update
  {
    foreach ($conditions as $column => $value) {
      $paramName = ":where_{$column}";
      if (is_int($value)) {
        $this->parameters[$paramName] = $value;
      } elseif (is_bool($value)) {
        $this->parameters[$paramName] = $value ? 'true' : 'false';
      } else {
        $this->parameters[$paramName] = "'$value'";
      }
      $this->where[] = "{$column} = {$paramName}";
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
    $setClause = implode(", ", array_map(fn($k, $v) => "$k = $v", array_keys($this->set), $this->set));
    $whereClause = implode(" AND ", $this->where);

    return "UPDATE {$this->table} SET $setClause" . ($whereClause ? " WHERE $whereClause" : "");
    ;
  }

  public function getParameters(): array
  {
    return $this->parameters;
  }
}
