<?php

namespace Ilias\Maestro\Core;

use Ilias\Maestro\Abstract\Database;
use Ilias\Maestro\Database\PDOConnection;
use Ilias\Maestro\Helpers\SchemaComparator;
use PDO;
use Ilias\Maestro\Exceptions\NotFinalExceptions;
use Ilias\Maestro\Interface\Sql;
use Ilias\Maestro\Abstract\Schema;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Interface\PostgresFunction;
use Ilias\Maestro\Utils\Utils;

/**
 * Class Database
 *
 * This class provides methods to create and manage a PostgreSQL database schema,
 * including creating schemas, tables, and foreign key constraints. It also provides
 * methods to insert, update, and select data from tables.
 *
 * @package Ilias\Maestro\Core
 */
class Manager
{
  public static array $idCreationPattern = [
    "SERIAL",
    "PRIMARY KEY"
  ];

  public PDO $pdo;
  public SchemaComparator $schemaComparator;
  private Synchronizer $synchronizer;

  public function __construct()
  {
    $this->pdo = PDOConnection::getInstance();
    $this->synchronizer = new Synchronizer($this->pdo);
    $this->schemaComparator = new SchemaComparator();
  }

  public function createDatabase(Database $database): array
  {
    $sql = [];
    $schemas = $database::getSchemas();
    foreach ($schemas as $schemaClass) {
      $schema = new $schemaClass();
      $sql[] = $this->createSchema($schema);
    }

    foreach ($schemas as $schemaClass) {
      $schema = new $schemaClass();
      $sql = [...$sql, ...$this->createTablesForSchema($schema)];
    }

    return $sql;
  }

  public function createSchema(Schema | string $schema): string
  {
    if (gettype($schema) === "string" && is_subclass_of($schema, Schema::class)) {
      if (!Utils::isFinalClass($schema)) {
        throw new NotFinalExceptions("The " . Utils::sanitizeForPostgres($schema) . " class was not identified as \"final\"");
      }

      $schemaName = call_user_func("{$schema}::getSanitizedName");
      return "CREATE SCHEMA IF NOT EXISTS \"{$schemaName}\";";
    }
    if (!Utils::isFinalClass($schema::class)) {
      throw new NotFinalExceptions("The " . Utils::sanitizeForPostgres($schema) . " class was not identified as \"final\"");
    }

    $schemaName = $schema::getSanitizedName();
    return "CREATE SCHEMA IF NOT EXISTS \"{$schemaName}\";";
  }

  public function createTablesForSchema(Schema $schema): array
  {
    $this->createSchema($schema);
    $sql = [];

    $tables = $schema::getTables();
    foreach ($tables as $tableClass) {
      $sql[] = $this->createTable($tableClass);
    }
    foreach ($tables as $tableClass) {
      $sql = array_merge($sql, $this->createForeignKeyConstraints($tableClass));
    }

    return $sql;
  }

  public function createTable(string $table): string
  {
    if (!Utils::isFinalClass($table)) {
      throw new NotFinalExceptions("The " . $table::getSanitizedName() . " class was not identified as \"final\"");
    }

    $tableName = $table::getSanitizedName();
    $columns = $table::getColumns();
    $uniqueColumns = $table::getUniqueColumns();
    $schemaName = $this->getSchemaNameFromTable($table);

    $columnDefs = [];
    $primaryKey = 'id';
    $reflectionClass = new \ReflectionClass($table);

    if (isset($columns[$primaryKey])) {
      $idColumnDef = Utils::sanitizeForPostgres($primaryKey) . " " . implode(" ", self::$idCreationPattern);
      $columnDefs[] = $idColumnDef;
      unset($columns[$primaryKey]);
    }

    foreach ($columns as $name => $type) {
      if (is_subclass_of($type, Table::class)) {
        $type = 'integer';
      }

      $sanitizedColumnName = Utils::sanitizeForPostgres($name);
      $columnType = is_array($type) ? $this->getColumnType($type[0]) : $this->getColumnType($type);

      $columnDef = "$sanitizedColumnName $columnType";

      if ($this->isPropertyNotNull($reflectionClass, $name)) {
        $columnDef .= " NOT NULL";
      } else {
        $columnDef .= " NULL";
      }

      $defaultValue = $this->getPropertyDefaultValue($reflectionClass, $name);
      if ($defaultValue !== null) {
        if (is_array($type) && $type[1] === PostgresFunction::class) {
          $columnDef .= " DEFAULT {$defaultValue}";
        } else {
          $columnDef .= " DEFAULT " . $this->formatDefaultValue($defaultValue);
        }
      }

      if (in_array($name, $uniqueColumns)) {
        $columnDef .= " UNIQUE";
      }

      $columnDefs[] = $columnDef;
    }

    $query = "CREATE TABLE IF NOT EXISTS \"$schemaName\".\"$tableName\"";
    $query .= " (\n\t" . implode(",\n\t", $columnDefs) . "\n);";

    return $query;
  }

  public function createForeignKeyConstraints(string $table): array
  {
    $schemaName = $this->getSchemaNameFromTable($table);
    $tableName = $table::getSanitizedName();
    $columns = $table::getColumns();
    $constraints = [];

    foreach ($columns as $name => $type) {
      if (is_subclass_of($type, Table::class)) {
        $referencedTable = $type::getSanitizedName();
        $referencedSchema = $this->getSchemaNameFromTable($type);

        $sanitizedName = Utils::sanitizeForPostgres($name);
        $constraints[] = "ALTER TABLE \"{$schemaName}\".\"{$tableName}\" ADD CONSTRAINT fk_{$tableName}_{$sanitizedName} FOREIGN KEY (\"{$sanitizedName}\") REFERENCES \"{$referencedSchema}\".\"{$referencedTable}\"(\"id\");";
      }
    }

    return $constraints;
  }

  public function isPropertyNotNull(\ReflectionClass $reflectionClass, string $propertyName): bool
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

  public function getSchemaNameFromTable($table): string
  {
    $reflectionClass = new \ReflectionClass($table);
    $schemaProperty = $reflectionClass->getProperty('schema');
    $schemaClass = $schemaProperty->getType()->getName();
    return Utils::sanitizeForPostgres((new \ReflectionClass($schemaClass))->getShortName());
  }

  public function getPropertyDefaultValue(\ReflectionClass $reflectionClass, string $propertyName)
  {
    $property = $reflectionClass->getProperty($propertyName);
    if ($property->isDefault() && $property->isPublic()) {
      $defaultValues = $reflectionClass->getDefaultProperties();
      return $defaultValues[$propertyName] ?? null;
    }
    return null;
  }

  public function formatDefaultValue(mixed $value): string
  {
    if (is_string($value)) {
      return "'" . addslashes($value) . "'";
    } elseif (is_bool($value)) {
      return $value ? 'TRUE' : 'FALSE';
    }
    return (string)$value;
  }

  public function getColumnType(string $type): string
  {
    return Utils::getPostgresType($type);
  }

  public function synchronizeSchema(Schema $schema): array
  {
    return $this->synchronizer->synchronizeSchema($schema);
  }

  public function synchronizeDatabase(Database $database): array
  {
    return $this->synchronizer->synchronizeDatabase($database);
  }

  public function executeQuery(PDO $pdo, Sql|string $sql)
  {
    if (is_string($sql)) {
      $stmt = $pdo->exec($sql);
    } else {
      $stmt = $pdo->prepare($sql->getSql());
      $stmt->execute($sql->getParameters());
    }
    return $stmt !== false;
  }
}
