<?php

namespace Ilias\Maestro\Core;

use PDO;
use InvalidArgumentException;
use Throwable;
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
 * Class Manager
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

  /**
   * Create the database schema.
   *
   * @param Database $database
   * @param bool $executeOnComplete
   * @return array
   */
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

  /**
   * Create a schema.
   *
   * @param string|Schema $schema
   * @return string
   * @throws InvalidArgumentException
   */
  public function createSchema(string|Schema $schema): string
  {
    if (is_string($schema) && is_subclass_of($schema, Schema::class)) {
      if (!Utils::isFinalClass($schema)) {
        throw new NotFinalExceptions("The " . Utils::sanitizeForPostgres($schema) . " class was not identified as \"final\".");
      }
      try {
        $schemaName = call_user_func("{$schema}::getSanitizedName");
        return $this->strictType === Maestro::SQL_STRICT
          ? "CREATE SCHEMA IF NOT EXISTS \"{$schemaName}\";"
          : "CREATE SCHEMA \"{$schemaName}\";";
      } catch (Throwable) {
        throw new InvalidArgumentException('The getSanitizedName method was not implemented in the provided schema class.');
      }
    }
    throw new InvalidArgumentException('The provided $schema is not a real schema. Use <SchemaClass>::class to get the full schema namespace.');
  }

  /**
   * Create tables for a schema.
   *
   * @param string|Schema $schema
   * @return array
   */
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

  /**
   * Create a table.
   *
   * @param string $table
   * @return string
   * @throws NotFinalExceptions
   */
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
      $isIdentifier = Utils::isIdentifier($type);
      $columnDef = $this->strictType === Maestro::SQL_STRICT
        ? "\"{$sanitizedColumnName}\" {$this->getColumnType($type)}"
        : "{$sanitizedColumnName} {$this->getColumnType($type)}";

      if (in_array($name, $notNullColumns) || $isIdentifier) {
        $columnDef .= ' NOT NULL';
      } elseif ($this->strictType === Maestro::SQL_STRICT) {
        $columnDef .= ' NULL';
      }

      if (isset($primaryColumn[$name])) {
        $columnDef .= ' ' . implode(' ', self::$idCreationPattern);
      }

      $defaultValue = $this->getPropertyDefaultValue($reflectionClass, $name);
      if ($defaultValue !== null) {
        $columnDef .= is_array($type) && $type[1] === Expression::class
          ? " DEFAULT {$defaultValue}"
          : " DEFAULT {$this->formatDefaultValue($defaultValue)}";
      }

      if (in_array($name, $uniqueColumns) || $isIdentifier) {
        $columnDef .= ' UNIQUE';
      }

      $columnDefs[] = $columnDef;
    }

    $query = $this->strictType === Maestro::SQL_STRICT
      ? "CREATE TABLE IF NOT EXISTS \"$schemaName\".\"$tableName\""
      : "CREATE TABLE \"$schemaName\".\"$tableName\"";
    $query .= " (\n\t" . implode(",\n\t", $columnDefs) . "\n);";

    return $query;
  }

  /**
   * Get the column type.
   *
   * @param mixed $type
   * @return string
   */
  public function getColumnType(mixed $type): string
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
    throw new InvalidArgumentException('Invalid column type provided.');
  }

  /**
   * Create foreign key constraints for a table.
   *
   * @param string $table
   * @return array
   */
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

  /**
   * Get not null properties of a class.
   *
   * @param \ReflectionClass $reflectionClass
   * @return array
   */
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

  /**
   * Get the schema name from a table.
   *
   * @param string $table
   * @return string
   */
  public function schemaNameFromTable(string $table): string
  {
    $reflectionClass = new \ReflectionClass($table);
    $schemaProperty = $reflectionClass->getProperty('schema');
    $schemaClass = $schemaProperty->getType()->getName();
    return Utils::sanitizeForPostgres((new \ReflectionClass($schemaClass))->getShortName());
  }

  /**
   * Get the default value of a property.
   *
   * @param \ReflectionClass $reflectionClass
   * @param string $propertyName
   * @return mixed
   */
  public function getPropertyDefaultValue(\ReflectionClass $reflectionClass, string $propertyName): mixed
  {
    $property = $reflectionClass->getProperty($propertyName);
    if ($property->isDefault() && $property->isPublic()) {
      $defaultValues = $reflectionClass->getDefaultProperties();
      return $defaultValues[$propertyName] ?? null;
    }
    return null;
  }

  /**
   * Format the default value for SQL.
   *
   * @param mixed $value
   * @return string
   */
  public function formatDefaultValue(mixed $value): string
  {
    if (is_string($value)) {
      return "'" . addslashes($value) . "'";
    } elseif (is_bool($value)) {
      return $value ? 'TRUE' : 'FALSE';
    }
    return (string) $value;
  }

  /**
   * Execute a query.
   *
   * @param PDO $pdo
   * @param Query|string $sql
   * @return array
   */
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
