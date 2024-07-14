<?php

namespace Ilias\Maestro\Core;

use PDO;
use Ilias\Maestro\Exceptions\NotFinalExceptions;
use Ilias\Maestro\Interface\Sql;
use Ilias\Maestro\Abstract\Schema;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Database\Update;
use Ilias\Maestro\Utils\Utils;

class Database
{
  public function createSchema(Schema $schema): string
  {
    $schemaName = $schema::getSchemaName();
    return "CREATE SCHEMA IF NOT EXISTS \"{$schemaName}\"";
  }

  public function createTablesForSchema(Schema $schema): array
  {
    $this->createSchema($schema);
    $sql = [];

    $tables = $schema::getTables();
    foreach ($tables as $tableName => $tableClass) {
      $sql[] = $this->createTable($tableClass);
    }
    foreach ($tables as $tableName => $tableClass) {
      $sql = array_merge($sql, $this->createForeignKeyConstraints($tableClass));
    }

    return $sql;
  }

  // TODO: find some way to identify if the column is `UNIQUE`
  public function createTable(string $table): string
  {
    if (!Utils::isFinalClass($table)) {
      throw new NotFinalExceptions("The " . $table::getSanitizedName() . " class was not identified as \"final\"");
    }

    $tableName = $table::getSanitizedName();
    $columns = $table::getColumns();
    $schemaName = $this->getSchemaNameFromTable($table);

    $columnDefs = [];
    $primaryKey = 'id';
    $reflectionClass = new \ReflectionClass($table);

    foreach ($columns as $name => $type) {
      if (is_subclass_of($type, Table::class)) {
        $type = 'integer';
      }

      $columnDef = Utils::sanitizeForPostgres($name) . " " . (($name === "id") ? "SERIAL PRIMARY KEY" : $this->getColumnType($type));

      if ($this->isPropertyNotNull($reflectionClass, $name)) {
        $columnDef .= " NOT NULL";
      }

      $defaultValue = $this->getPropertyDefaultValue($reflectionClass, $name);
      if ($defaultValue !== null) {
        $columnDef .= " DEFAULT " . $this->formatDefaultValue($defaultValue);
      }

      $columnDefs[] = $columnDef;
    }

    $query = "CREATE TABLE IF NOT EXISTS \"$schemaName\".\"$tableName\"";
    $query .= " (\n\t" . implode(",\n\t", $columnDefs) . "\n);";

    return $query;
  }

  private function createForeignKeyConstraints(string $table): array
  {
    $schemaName = $this->getSchemaNameFromTable($table);
    $tableName = $table::getSanitizedName();
    $columns = $table::getColumns();
    $constraints = [];
    $reflectionClass = new \ReflectionClass($table);

    foreach ($columns as $name => $type) {
      if (is_subclass_of($type, Table::class)) {
        $referencedTable = $type::getSanitizedName();
        $referencedSchema = $this->getSchemaNameFromTable($type);
        $constraints[] = "ALTER TABLE \"{$schemaName}\".\"{$tableName}\" ADD CONSTRAINT fk_{$tableName}_{$name} FOREIGN KEY (\"" . Utils::sanitizeForPostgres($name) . "\") REFERENCES \"{$referencedSchema}\".\"{$referencedTable}\"(\"id\");";
      }
    }

    return $constraints;
  }

  private function getSchemaNameFromTable($table): string
  {
    $reflectionClass = new \ReflectionClass($table);
    $schemaProperty = $reflectionClass->getProperty('schema');
    $schemaClass = $schemaProperty->getType()->getName();
    return Utils::sanitizeForPostgres((new \ReflectionClass($schemaClass))->getShortName());
  }

  private function isPropertyNotNull(\ReflectionClass $reflectionClass, string $propertyName): bool
  {
    $constructor = $reflectionClass->getConstructor();
    if ($constructor) {
      $params = $constructor->getParameters();
      foreach ($params as $param) {
        if ($param->getName() === $propertyName && !$param->isOptional()) {
          return true;
        }
      }
    }
    return false;
  }

  private function getPropertyDefaultValue(\ReflectionClass $reflectionClass, string $propertyName)
  {
    $property = $reflectionClass->getProperty($propertyName);
    if ($property->isDefault() && $property->isPublic()) {
      $defaultValues = $reflectionClass->getDefaultProperties();
      return $defaultValues[$propertyName] ?? null;
    }
    return null;
  }

  private function formatDefaultValue(mixed $value): string
  {
    if (is_string($value)) {
      return "'" . addslashes($value) . "'";
    } elseif (is_bool($value)) {
      return $value ? 'TRUE' : 'FALSE';
    }
    return (string)$value;
  }

  private function getColumnType(string $type): string
  {
    return Utils::getPostgresType($type);
  }

  public function insertIntoTable(Table $table, Table | array $data): Insert
  {
    $insert = new Insert();
    return $insert->into($table::getTableName())->values($data);
  }

  public function updateTable(Table $table, array $data, array $conditions): Update
  {
    $update = new Update();
    $query = $update->table($table::getTableName());

    foreach ($data as $column => $value) {
      $query->set($column, $value);
    }

    foreach ($conditions as $condition => $params) {
      $query->where($condition, $params);
    }

    return $query;
  }

  public function selectFromTable(Table $table, array $columns = ['*'], array $conditions = []): Select
  {
    $select = new Select();
    $query = $select->select(...$columns)->from($table::getTableName());

    foreach ($conditions as $condition => $params) {
      $query->where($condition, $params);
    }

    return $query;
  }

  public function executeQuery(PDO $pdo, Sql|string $sql)
  {
    if (is_string($sql)) {
      $stmt = $pdo->exec($sql);
    } else {
      $stmt = $pdo->prepare($sql->getSql());
      $stmt->execute($sql->getParameters());
    }
    return $stmt;
  }
}
