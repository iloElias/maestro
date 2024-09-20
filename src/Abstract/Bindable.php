<?php

namespace Ilias\Maestro\Abstract;

use Ilias\Maestro\Database\PDOConnection;
use Ilias\Maestro\Database\SqlBehavior;
use InvalidArgumentException, PDO;

abstract class Bindable
{
  public mixed $current = null;
  protected array $parameters = [];

  public function __construct(
    private string $behavior = SqlBehavior::SQL_STRICT,
  ) {
  }

  abstract public function getSql(): string;
  abstract public function getParameters(): array;

  protected function validateSelectTable(array $table): array
  {
    foreach ($table as $key => $value) {
      if (is_int($key)) {
        throw new InvalidArgumentException("Table alias cannot be a number.");
      }
      $name = $this->validateTableName($value);
      return [$name, $key];
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
          case SqlBehavior::SQL_STRICT:
            throw new InvalidArgumentException("In strict SQL mode, table names must be provided as schema.table.");
          case SqlBehavior::SQL_PREDICT:
            return "public.{$table}";
          case SqlBehavior::SQL_NO_PREDICT:
            return $table;
        }
      }
      return $table;
    }
  }

  public function bindParameters()
  {
    foreach ($this->parameters as $key => $value) {
      if (is_null($value)) {
        $this->parameters[$key] = PDO::PARAM_NULL;
      } elseif (is_bool($value)) {
        $this->parameters[$key] = PDO::PARAM_BOOL;
      } elseif (is_int($value)) {
        $this->parameters[$key] = PDO::PARAM_INT;
      } elseif (is_string($value)) {
        $this->parameters[$key] = PDO::PARAM_STR;
      } else {
        throw new InvalidArgumentException("Unsupported parameter type: " . gettype($value));
      }
    }
  }

  public function execute(PDO $pdo = null): array
  {
    if (empty($pdo)) {
      $pdo = PDOConnection::getInstance();

    }
    $stmt = $pdo->prepare($this->getSql());
    foreach ($this->getParameters() as $key => $value) {
      $stmt->bindValue($key, $value, $this->parameters[$key]);
    }
    if (!$stmt->execute()) {
      throw new \RuntimeException("Failed to execute statement: " . implode(", ", $stmt->errorInfo()));
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
