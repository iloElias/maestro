<?php

namespace Ilias\Maestro\Database;

use Ilias\Maestro\Abstract\Bindable;
use Ilias\Maestro\Abstract\SqlBehavior;
use Ilias\Maestro\Interface\Sql;

class Select extends Bindable
{
  private string $from;
  private array $columns = [];
  private array $joins = [];
  private array $where = [];
  private array $order = [];
  private string $limit = '';

  /**
   * Sets the table and columns for the SELECT statement.
   *
   * @param array $table An array containing the table name and alias. It should be provided like `[$alias => $table]`, the `$table` being the schema name and the table name separated by a dot, basically like `"{$schema}.{$table}"`. The `$table` can also be provided as class name from a Table inherited class: `$table = User::class`.
   * @param array $columns An array of columns to select, with optional renaming.
   * @return Select Returns the current Select instance.
   */
  public function from(array $table, array $columns = [Sql::SQL_STAR]): Select
  {
    [$name, $alias] = $this->validateSelectTable($table);
    $this->from = $name;

    foreach ($columns as $rename => $column) {
      $holder = is_string($column) ? "{$alias}.{$column}" : (string) $column;
      $this->columns[] = is_int($rename) ? $holder : "{$holder} AS {$rename}";
    }

    return $this;
  }

  /**
   * Adds a join clause to the select query.
   *
   * @param array $table An array containing the table name and alias. It should be provided like `[$alias => $table]`, the `$table` being the schema name and the table name separated by a dot, basically like `"{$schema}.{$table}"`. The `$table` can also be provided as class name from a Table inherited class: `$table = User::class`.
   * @param string $condition The join condition.
   * @param array $columns Optional. The columns to select from the joined table, specified as an array. Default is an empty array.
   * @param string $type Optional. The type of join to perform (e.g., 'INNER', 'LEFT'). Default is 'INNER'.
   * @return Select Returns the current Select instance for method chaining.
   */
  public function join(array $table, string $condition, array $columns = [], string $type = 'INNER'): Select
  {
    [$name, $alias] = $this->validateSelectTable($table);
    $this->joins[] = strtoupper($type) . " JOIN {$name} AS {$alias} ON {$condition}";

    foreach ($columns as $rename => $column) {
      $holder = is_string($column) ? "{$alias}.{$column}" : (string) $column;
      $this->columns[] = is_int($rename) ? $holder : "{$holder} AS {$rename}";
    }

    return $this;
  }

  /**
   * Adds WHERE conditions to the SQL query.
   * This method accepts an associative array of conditions where the key is the column name and the value is the condition value.
   *
   * @param array $conditions An associative array of conditions for the WHERE clause.
   * @return $this Returns the current instance for method chaining.
   */
  public function where(array $conditions): Select
  {
    foreach ($conditions as $column => $value) {
      $paramName = str_replace('.', '_', ":where_{$column}");
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

  public function limit(int|string $limit): Select
  {
    $this->limit = "{$limit}";
    return $this;
  }

  public function getSql(): string
  {
    $columns = implode(", ", $this->columns);
    $joins = implode(" ", $this->joins);
    $whereClause = implode(" AND ", $this->where);
    $orderClause = implode(", ", $this->order);

    $sql = [];
    $sql[] = "SELECT $columns FROM {$this->from}";
    if (!empty($joins)) {
      $sql[] = $joins;
    }
    if (!empty($whereClause)) {
      $sql[] = "WHERE {$whereClause}";
    }
    if (!empty($orderClause)) {
      $sql[] = "ORDER BY {$orderClause}";
    }
    if (!empty($this->limit)) {
      $sql[] = "LIMIT {$this->limit}";
    }

    return implode(" ", $sql);
  }

  public function getParameters(): array
  {
    return $this->parameters;
  }
}
