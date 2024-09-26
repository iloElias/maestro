<?php

namespace Ilias\Maestro\Core;

use PDO ,InvalidArgumentException ,Throwable;
use Ilias\Maestro\Abstract\Database;
use Ilias\Maestro\Abstract\Identifier;
use Ilias\Maestro\Abstract\Query;
use Ilias\Maestro\Abstract\Schema;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Database\PDOConnection;
use Ilias\Maestro\Exceptions\NotFinalExceptions;
use Ilias\Maestro\Database\Expression;
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
    'PRIMARY KEY',
  ];
  public PDO $pdo;

  public function __construct(
    private string $strictType = Maestro::SQL_STRICT
  ) {
    $this->pdo = PDOConnection::getInstance();
  }

  public function createDatabase(Database $database, bool $executeOnComplete = true): array
  {
    $schemasSql = [];
    $tablesSql = [];
    $constraintsSql = [];
    $schemas = $database::getDatabaseSchemas();

    foreach ($schemas as $schemaClass) {
      $schemasSql[] = $this->createSchema($schemaClass);
      [$create, $constraints] = $this->createSchemaTables($schemaClass);
      $tablesSql = array_merge($tablesSql, $create);
      $constraintsSql = array_merge($constraintsSql, ...$constraints);
    }
    $sql = array_merge($schemasSql, $tablesSql, $constraintsSql);
    if ($executeOnComplete) {
      foreach ($sql as $query) {
        $this->executeQuery($this->pdo, $query);
      }
    }

    return $sql;
  }

  public function createSchema(string|Schema $schema): string
  {
    if (gettype($schema) === "string" && is_subclass_of($schema, Schema::class)) {
      if (!Utils::isFinalClass($schema)) {
        throw new NotFinalExceptions("The " . Utils::sanitizeForPostgres($schema) . " class was not identified as \"final\".");
      }
      try {
        $schemaName = call_user_func("{$schema}::getSanitizedName");
        if ($this->strictType === Maestro::SQL_STRICT) {
          return "CREATE SCHEMA IF NOT EXISTS \"{$schemaName}\";";
        } else {
          return "CREATE SCHEMA \"{$schemaName}\";";
        }
      } catch (Throwable) {
        throw new InvalidArgumentException('The getSanitizedName method was not implemented in the provided schema class.');
      }
    }
    throw new InvalidArgumentException('The provided $schema is not a real schema. Is recommended to use <SchemaClass>::class to get the full schema namespace.', 1);
  }

  public function createSchemaTables(string|Schema $schema): array
  {
    $create = [];
    $constraints = [];

    $tables = $schema::getSchemaTables();
    foreach ($tables as $tableClass) {
      $create[] = $this->createTable($tableClass);
    }
    foreach ($tables as $tableClass) {
      $constraints[] = $this->createForeignKeyConstraints($tableClass);
    }

    return [$create, $constraints];
  }

  public function createTable(string $table): string
  {
    if (!Utils::isFinalClass($table)) {
      throw new NotFinalExceptions("The " . $table::getSanitizedName() . " class was not identified as \"final\"");
    }

    $reflectionClass = new \ReflectionClass($table);

    $tableName = $table::getSanitizedName();
    $columns = $table::tableColumns();
    $primaryColumn = $table::tableIdentifier();
    $uniqueColumns = $table::getUniqueColumns();
    $notNullColumns = $this->getNotNullProperties($reflectionClass);
    $schemaName = $this->schemaNameFromTable($table);

    $columnDefs = [];

    foreach ($columns as $name => $type) {
      $sanitizedColumnName = Utils::sanitizeForPostgres($name);
      $isIdentifier =  Utils::isIdentifier($type);
      if ($this->strictType === Maestro::SQL_STRICT) {
        $columnDef = "\"{$sanitizedColumnName}\" {$this->getColumnType($type)}";
      } else {
        $columnDef = "{$sanitizedColumnName} {$this->getColumnType($type)}";
      }

      if (in_array($name, $notNullColumns) || $isIdentifier) {
        $columnDef .= ' NOT NULL';
      } else {
        if ($this->strictType === Maestro::SQL_STRICT) {
          $columnDef .= ' NULL';
        }
      }

      if (isset($primaryColumn[$name])) {
        $columnDef .= (' ' . implode(' ', self::$idCreationPattern));
      }

      $defaultValue = $this->getPropertyDefaultValue($reflectionClass, $name);
      if ($defaultValue !== null) {
        if (is_array($type) && $type[1] === Expression::class) {
          $columnDef .= " DEFAULT {$defaultValue}";
        } else {
          $columnDef .= " DEFAULT {$this->formatDefaultValue($defaultValue)}";
        }
      }

      if (in_array($name, $uniqueColumns) || $isIdentifier) {
        $columnDef .= ' UNIQUE';
      }

      $columnDefs[] = $columnDef;
    }

    if ($this->strictType === Maestro::SQL_STRICT) {
      $query = "CREATE TABLE IF NOT EXISTS \"$schemaName\".\"$tableName\"";
    } else {
      $query = "CREATE TABLE \"$schemaName\".\"$tableName\"";
    }
    $query .= " (\n\t" . implode(",\n\t", $columnDefs) . "\n);";

    return $query;
  }

  public function getColumnType($type)
  {
    if (is_subclass_of($type, Table::class)) {
      foreach ($type::tableIdentifier() as $value) {
        return $value::tableIdentifierReferenceType();
      }
    }
    if (is_subclass_of($type, Identifier::class)) {
      return $type::tableIdentifierType();
    }
    if (is_array($type)) {
      return $this->getColumnType($type[0]);
    }
    if (is_string($type)) {
      return Utils::getPostgresType($type);
    }
  }

  public function createForeignKeyConstraints(string $table): array
  {
    $schemaName = $this->schemaNameFromTable($table);
    $tableName = $table::getSanitizedName();
    $columns = $table::tableColumns();
    $constraints = [];

    foreach ($columns as $name => $type) {
      if (is_subclass_of($type, Table::class)) {
        $referencedTable = $type::getSanitizedName();
        $referencedSchema = $this->schemaNameFromTable($type);

        $sanitizedName = Utils::sanitizeForPostgres($name);
        $constraints[] = "ALTER TABLE \"{$schemaName}\".\"{$tableName}\" ADD CONSTRAINT fk_{$tableName}_{$sanitizedName} FOREIGN KEY (\"{$sanitizedName}\") REFERENCES \"{$referencedSchema}\".\"{$referencedTable}\"(\"id\");";
      }
    }

    return $constraints;
  }

  public function getNotNullProperties(\ReflectionClass $reflectionClass): array
  {
    $properties = [];
    $constructor = $reflectionClass->getConstructor();
    if ($constructor) {
      $params = $constructor->getParameters();
      foreach ($params as $param) {
        if (!$param->isOptional()) {
          $properties[] = $param->getName();
        }
      }
    }
    foreach ($reflectionClass->name::tableColumnsProperties(Maestro::DOC_NOT_NUABLE) as $value) {
      if (!in_array($value, $properties)) {
        $properties[] = $value;
      }
    }
    return $properties;
  }

  public function schemaNameFromTable($table): string
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
    return (string) $value;
  }


  public function executeQuery(PDO $pdo, Query|string $sql): array
  {
    if (is_string($sql)) {
      $result = $pdo->exec($sql);
      if (is_object($result)) {
        return $result->fetchAll(PDO::FETCH_ASSOC);
      }
      return [];
    } else {
      return $sql->bindParameters($pdo)->execute();
    }
  }
}
