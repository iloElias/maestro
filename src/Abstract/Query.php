<?php

namespace Ilias\Maestro\Abstract;

use Ilias\Maestro\Core\Maestro;
use Ilias\Maestro\Database\Expression;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Utils\Utils;
use InvalidArgumentException, PDO, Exception, PDOStatement;

abstract class Query
{
  public mixed $current = null;
  protected array $parameters = [];
  protected array $where = [];
  private ?PDOStatement $stmt = null;
  private bool $isBinded = false;
  protected string $query = ''; 
  public const AND = 'AND';
  public const OR = 'OR';

  public function __construct(
    protected string $behavior = Maestro::SQL_STRICT,
    private ?PDO $pdo = null,
  ) {
  }

  /**
   * Adds WHERE conditions to the SQL query.
   * This method accepts an associative array of conditions where the key is the column name and the value is the condition value.
   *
   * @param array $conditions An associative array of conditions for the WHERE clause.
   * @return $this Returns the current instance for method chaining.
   */
  public function where(array $conditions, string $operation = Select::AND, bool $group = false): static
  {
    $where = [];
    foreach ($conditions as $column => $value) {
      $columnWhere = Utils::sanitizeForPostgres($column);
      $paramName = str_replace('.', '_', ":where_{$columnWhere}");
      $this->storeParameter($paramName, $value);
      $where[] = "{$column} = {$paramName}";
    }
    $clauses = implode(" {$operation} ", $where);
    $this->where[] = ($group ? "({$clauses})" : $clauses);
    return $this;
  }

  public function in(array $conditions, string $operation = Select::AND, bool $group = false): static
  {
    $where = [];
    foreach ($conditions as $column => $value) {
      $inParams = array_map(function ($v, $k) use ($column) {
        $columnIn = Utils::sanitizeForPostgres($column);
        $paramName = ":in_{$columnIn}_{$k}";
        $this->storeParameter($paramName, $v);
        return $paramName;
      }, $value, array_keys($value));
      $inList = implode(",", $inParams);
      $where[] = "{$column} IN({$inList})";
    }
    $clauses = implode(" {$operation} ", $where);
    $this->where[] = ($group ? "({$clauses})" : $clauses);
    return $this;
  }

  protected function storeParameter(string $name, mixed $value): void
  {
    if (!is_bool($value) && in_array($value, Expression::DEFAULT_REPLACE_EXPRESSIONS)) {
      $this->parameters[$name] = Expression::DEFAULT;
      return;
    }
    if (is_null($value)) {
      $this->parameters[$name] = Expression::NULL;
    } elseif (is_int($value)) {
      $this->parameters[$name] = $value;
    } elseif (is_bool($value)) {
      $this->parameters[$name] = $value ? Expression::TRUE : Expression::FALSE;
    } elseif (is_object($value) && is_subclass_of($value, Query::class)) {
      $this->parameters[$name] = "({$value})";
    } elseif (is_object($value) && $value instanceof Expression) {
      $this->parameters[$name] = "{$value}";
    } else {
      $value = str_replace("'", "''", $value);
      $this->parameters[$name] = "'{$value}'";
    }
  }

  abstract public function getSql(): string;

  public function getParameters(): array
  {
    return $this->parameters;
  }

  protected function validateSelectTable(array $table): array
  {
    foreach ($table as $key => $value) {
      if (is_int($key)) {
        $name = $this->validateTableName($value);
        $name = Utils::toSnakeCase($name);
        return [$name, $name];
      }
      $alias = Utils::toSnakeCase($key);
      if (is_string($value)) {
        $name = $this->validateTableName($value);
        return [Utils::toSnakeCase($name), $alias];
      }
      if (is_object($value) && is_subclass_of($value, Query::class)) {
        return ["({$value})", $alias];
      }
    }
    return [];
  }

  protected function validateTableName(string $table): string
  {
    if (empty($table)) {
      throw new InvalidArgumentException("Table name cannot be empty.");
    }

    try {
      return call_user_func("{$table}::getTableSchemaAddress");
    } catch (\Throwable $e) {
      if (!str_contains($table, ".")) {
        switch ($this->behavior) {
          case Maestro::SQL_STRICT:
            throw new InvalidArgumentException("In strict SQL mode, table names must be provided as schema.table.");
          case Maestro::SQL_PREDICT:
            return "public.{$table}";
          case Maestro::SQL_NO_PREDICT:
            return $table;
        }
      }
      return $table;
    }
  }

  public function bindParameters(?PDO $pdo = null): Query
  {
    $query = $this->getSql();
    foreach ($this->parameters as $key => $value) {
      $query = str_replace($key, $value, $query);
    }
    $this->query = $query;
    if (!empty($this->pdo) || !empty($pdo)) {
      if (!$this->isBinded) {
        $stmt = $this->pdo->prepare($query);
        $this->isBinded = true;
        $this->stmt = $stmt;
      }
    }
    return $this;
  }

  public function execute(): array
  {
    if (!$this->isBinded) {
      $this->bindParameters();
    }
    if (!empty($this->stmt)) {
      $this->stmt->execute();
      return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    throw new Exception("No PDOStatement object found.");
  }

  public function __toString(): string
  {
    return $this->bindParameters()->query;
  }
}
