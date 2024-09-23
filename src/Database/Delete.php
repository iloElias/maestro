<?php

namespace Ilias\Maestro\Database;

use Ilias\Maestro\Abstract\Query;
use Ilias\Maestro\Utils\Utils;

class Delete extends Query
{
  private string $table;
  private array $where = [];

  public function from(string $table): Delete
  {
    $this->table = $this->validateTableName($table);
    return $this;
  }

  /**
   * Adds WHERE conditions to the SQL query.
   * This method accepts an associative array of conditions where the key is the column name and the value is the condition value.
   *
   * @param array $conditions An associative array of conditions for the WHERE clause.
   * @return $this Returns the current instance for method chaining.
   */
  public function where(array $conditions): Delete
  {
    foreach ($conditions as $column => $value) {
      $columnWhere = Utils::sanitizeForPostgres($column);
      $paramName = ":where_{$columnWhere}";
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

  /**
   * Adds an IN condition to the delete query.
   *
   * This method allows you to specify multiple conditions for the delete query using the SQL IN clause. Each condition is an associative array where the key is the column name and the value is an array of values to match against.
   *
   * @param array $conditions An associative array of conditions where the key is the column name and the value is an array of values.
   * @return Delete Returns the current instance of the Delete class for method chaining.
   */
  public function in(array $conditions): Delete
  {
    foreach ($conditions as $column => $value) {
      $inParams = array_map(function ($v, $k) use ($column) {
        $columnIn = Utils::sanitizeForPostgres($column);
        $paramName = ":in_{$columnIn}_{$k}";
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

    return "DELETE FROM {$this->table}" . ($whereClause ? " WHERE {$whereClause}" : "");
  }

  public function getParameters(): array
  {
    return $this->parameters;
  }
}
