<?php

namespace Ilias\Maestro\Database;

use Ilias\Maestro\Abstract\Query;
use Ilias\Maestro\Core\Maestro;
use Ilias\Maestro\Utils\Utils;

class Select extends Query
{
  const STAR = '*';
  const INNER = 'INNER';
  const LEFT = 'LEFT';
  const RIGHT = 'RIGHT';
  const ORDER_ASC = 'ASC';
  const ORDER_DESC = 'DESC';

  private string $from;
  private string $alias;
  private array $columns = [];
  private array $joins = [];
  private string $group = '';
  private array $having = [];
  private array $order = [];
  private string $offset = '';
  private string $limit = '';
  private bool $distinct = false;

  /**
   * Sets the DISTINCT flag for the SELECT statement.
   *
   * @param bool $distinct Whether to select distinct rows.
   * @return Select Returns the current Select instance.
   */
  public function distinct(bool $distinct = true): Select
  {
    $this->distinct = $distinct;
    return $this;
  }

  /**
   * Sets the table and columns for the SELECT statement.
   *
   * @param array $table An array containing the table name and alias. It should be provided like `[$alias => $table]`, the `$table` being the schema name and the table name separated by a dot, basically like `"{$schema}.{$table}"`. The `$table` can also be provided as class name from a Table inherited class: `$table = User::class`.
   * @param array $columns An array of columns to select, with optional renaming.
   * @return Select Returns the current Select instance.
   */
  public function from(array $table, array $columns = [Select::STAR]): Select
  {
    [$name, $alias] = $this->validateSelectTable($table);
    $this->from = $name;
    $this->alias = $alias;

    foreach ($columns as $rename => $column) {
      if ($column instanceof Expression) {
        $holder = (string) $column;
      } elseif (is_subclass_of($column, Query::class)) {
        $holder = "({$column})";
      } else {
        $holder = is_string($column) ? "{$alias}.{$column}" : (string) $column;
      }
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
  public function join(array $table, string $condition, array $columns = [], string $type = Select::INNER): Select
  {
    [$name, $alias] = $this->validateSelectTable($table);
    $this->joins[] = strtoupper($type) . " JOIN {$name} AS {$alias} ON {$condition}";

    foreach ($columns as $rename => $column) {
      $holder = is_string($column) ? "{$alias}.{$column}" : (string) $column;
      $this->columns[] = is_int($rename) ? $holder : "{$holder} AS {$rename}";
    }

    return $this;
  }

  public function group(array $columns): Select
  {
    $this->group = implode(", ", $columns);
    return $this;
  }

  public function having(array $conditions): Select
  {
    foreach ($conditions as $column => $value) {
      $columnHaving = Utils::sanitizeForPostgres($column);
      $paramName = ":having_{$columnHaving}";
      $this->storeParameter($paramName, $value);
      $this->having[] = "{$column} = {$paramName}";
    }
    return $this;
  }

  public function order(string $column, string $direction = 'ASC'): Select
  {
    $this->order[] = "{$column} {$direction}";
    return $this;
  }

  public function offset(int|string $offset): Select
  {
    $this->offset = "{$offset}";
    return $this;
  }

  public function limit(int|string $limit): Select
  {
    if (is_string($limit) && !is_numeric($limit)) {
      throw new \InvalidArgumentException("Limit must be a number.");
    }
    $this->limit = "{$limit}";
    return $this;
  }

  public function getSql(): string
  {
    $columns = implode(", ", $this->columns);
    $joins = implode(" ", $this->joins);
    $whereClause = implode(" AND ", $this->where);
    $groupClause = $this->group;
    $havingClause = implode(" AND ", $this->having);
    $orderClause = implode(", ", $this->order);

    $sql = [];
    $distinct = $this->distinct ? ' DISTINCT' : '';
    if (in_array($this->behavior, [Maestro::SQL_STRICT, Maestro::SQL_PREDICT]) || !empty($joins)) {
      $sql[] = "SELECT{$distinct} $columns FROM {$this->from} AS {$this->alias}";
    } else {
      $sql[] = "SELECT{$distinct} $columns FROM {$this->from}";
    }
    if (!empty($joins)) {
      $sql[] = $joins;
    }
    if (!empty($whereClause)) {
      $sql[] = "WHERE {$whereClause}";
    }
    if (!empty($groupClause)) {
      $sql[] = "GROUP BY {$groupClause}";
    }
    if (!empty($havingClause)) {
      $sql[] = "HAVING {$havingClause}";
    }
    if (!empty($orderClause)) {
      $sql[] = "ORDER BY {$orderClause}";
    }
    if (!empty($this->limit)) {
      $sql[] = "LIMIT {$this->limit}";
    }
    if (!empty($this->offset)) {
      $sql[] = "OFFSET {$this->offset}";
    }

    return implode(" ", $sql);
  }
}
