<?php

namespace Ilias\Maestro\Database;

use Ilias\Maestro\Abstract\Query;
use Ilias\Maestro\Utils\Utils;

class Update extends Query
{
  private $table;
  private $set = [];

  /**
   * Sets the table name for the update operation after validating it.
   * @param string $table The name of the table to update.
   * @return Update Returns the current instance for method chaining.
   */
  public function table(string $table): Update
  {
    $this->table = $this->validateTableName($table);
    return $this;
  }

  /**
   * Sets the value for a column or multiple columns in the update statement.
   * @param string|array $column The column name as a string or an associative array of column-value pairs.
   * @param mixed $value The value to set for the column. This parameter is ignored if $column is an array.
   * @return Update Returns the current instance of the Update class.
   * @throws \InvalidArgumentException If the column name is an integer or numeric string.
   */
  public function set(string|array $column, $value = null): Update
  {
    if (is_array($column)) {
      foreach ($column as $col => $val) {
        $this->set($col, $val);
      }
    }
    if (is_string($column)) {
      if (is_int($column) || is_numeric($column)) {
        throw new \InvalidArgumentException("Column name must be a string");
      }
      $column = Utils::sanitizeForPostgres($column);
      $paramName = ":$column";
      $this->set[$column] = $paramName;
      $this->storeParameter($paramName, $value);
    }
    return $this;
  }

  public function getSql(): string
  {
    $setClause = implode(", ", array_map(fn($k, $v) => "$k = $v", array_keys($this->set), $this->set));
    $whereClause = implode(" AND ", $this->where);

    return "UPDATE {$this->table} SET $setClause" . ($whereClause ? " WHERE $whereClause" : "");
  }
}
