<?php

namespace Ilias\Maestro\Core;

use PDO, InvalidArgumentException, Throwable;
use Ilias\Maestro\Abstract\Database;
use Ilias\Maestro\Abstract\Query;
use Ilias\Maestro\Abstract\Schema;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Database\PDOConnection;
use Ilias\Maestro\Exceptions\NotFinalExceptions;
use Ilias\Maestro\Utils\Utils;

/**
 * Class Manager
 * This class provides methods to create and manage a PostgreSQL database schema, including creating schemas, tables, and foreign key constraints. It also provides methods to insert, update, and select data from tables.
 */
class Manager
{
  public static array $idCreationPattern = [
    'PRIMARY KEY',
  ];
  public PDO $pdo;

  public function __construct(
    private string $buildMode = Maestro::SQL_STRICT
  ) {
    Maestro::handle();
    $this->pdo = PDOConnection::get();
  }

  /**
   * Create the database schema.
   * @param Database $database
   * @param bool $executeOnComplete
   * @return array
   */
  public function createDatabase(Database $database, bool $executeOnComplete = true): array
  {
    $schemasSql = [];
    $enumsSql = [];
    $tablesSql = [];
    $constraintsSql = [];
    $functionsSql = $this->createDatabaseFunctions($database);

    foreach ($database::getSchemas() as $schemaClass) {
      $schemasSql[] = $this->createSchema($schemaClass);
      [$create, $constraints] = $this->createSchemaTables($schemaClass);
      $tablesSql = array_merge($tablesSql, $create);
      $constraintsSql = array_merge($constraintsSql, ...$constraints);
    }

    foreach ($database::getEnums() as $enums) {
      $enumsSql[] = $this->createEnums($enums);
    }

    $sql = array_merge($functionsSql, $schemasSql, $enumsSql, $tablesSql, $constraintsSql);
    if ($executeOnComplete) {
      foreach ($sql as $query) {
        $this->executeQuery($this->pdo, $query);
      }
    }

    return $sql;
  }

  public function createEnums(string $enum): string
  {
    if (!enum_exists($enum)) {
      throw new InvalidArgumentException("Enum class {$enum} does not exist.");
    }
    $spreadEnumName = explode("\\", static::class);
    $enumName = Utils::sanitizeForPostgres($spreadEnumName[count($spreadEnumName) - 1]);
    $enumValues = implode(",", array_map(fn($case) => "'{$case->value}'", $enum::cases()));
    return "CREATE TYPE {$enumName} AS ENUM ({$enumValues});";
  }

  /**
   * Create a schema.
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
        $schemaName = call_user_func("{$schema}::sanitizedName");
        return $this->buildMode === Maestro::SQL_STRICT
          ? "CREATE SCHEMA IF NOT EXISTS \"{$schemaName}\";"
          : "CREATE SCHEMA {$schemaName};";
      } catch (Throwable) {
        throw new InvalidArgumentException('The sanitizedName method was not implemented in the provided schema class.');
      }
    }
    throw new InvalidArgumentException('The provided $schema is not a real schema. Use <SchemaClass>::class to get the full schema namespace.');
  }

  /**
   * Create tables for a schema.
   * @param string|Schema $schema
   * @return array
   */
  public function createSchemaTables(string|Schema $schema): array
  {
    $create = [];
    $constraints = [];

    $tables = $schema::getTables();
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
   * @param string $table
   * @return string
   * @throws NotFinalExceptions
   */
  public function createTable(string $table): string
  {
    if (!Utils::isFinalClass($table)) {
      throw new NotFinalExceptions("The " . $table::sanitizedName() . " class was not identified as \"final\"");
    }

    $schemaName = $this->schemaNameFromTable($table);
    $tableName = $table::sanitizedName();
    $columns = $table::tableCreationInfo();

    $columnDefs = [];

    foreach ($columns as $column) {
      $columnDef = $this->buildMode === Maestro::SQL_STRICT
        ? "\"{$column['name']}\" {$column['type']}"
        : "{$column['name']} {$column['type']}";

      if ($column['not_null'] || $column['primary']) {
        $columnDef .= ' NOT NULL';
      } elseif ($this->buildMode === Maestro::SQL_STRICT) {
        $columnDef .= ' NULL';
      }

      if ($column['primary']) {
        $columnDef .= ' ' . implode(' ', self::$idCreationPattern);
      }

      if (isset($column['default'])) {
        $columnDef .= " DEFAULT {$column['default']}";
      }

      if ($column['unique'] || $column['primary']) {
        $columnDef .= ' UNIQUE';
      }

      $columnDefs[] = $columnDef;
    }

    $query = $this->buildMode === Maestro::SQL_STRICT
      ? "CREATE TABLE IF NOT EXISTS \"{$schemaName}\".\"{$tableName}\""
      : "CREATE TABLE {$schemaName}.{$tableName}";
    $query .= " (\n\t" . implode(",\n\t", $columnDefs) . "\n);";

    return $query;
  }

  /**
   * Create functions for a schema.
   * @param string|Schema $schema
   * @return array
   */
  public function createDatabaseFunctions(string|Database $database): array
  {
    $functionsSql = [];
    new $database();
    $functions = $database::getFunctions();

    foreach ($functions as $function) {
      $functionsSql[] = $function->getSqlDefinition();
    }

    return $functionsSql;
  }

  /**
   * Create foreign key constraints for a table.
   * @param string $table
   * @return array
   */
  public function createForeignKeyConstraints(string $table): array
  {
    $schemaName = $this->schemaNameFromTable($table);
    $tableName = $table::sanitizedName();
    $constraints = [];
    foreach ($table::tableColumns() as $name => $type) {
      if (is_array($type)) {
        $type = $type[0];
      }
      if (is_subclass_of($type, Table::class)) {
        $referencedTable = $type::sanitizedName();
        $referencedSchema = $this->schemaNameFromTable($type);
        $sanitizedName = Utils::toSnakeCase($name);
        $constraints[] = "ALTER TABLE \"{$schemaName}\".\"{$tableName}\" ADD CONSTRAINT fk_{$tableName}_{$sanitizedName} FOREIGN KEY (\"{$sanitizedName}\") REFERENCES \"{$referencedSchema}\".\"{$referencedTable}\"(\"id\");";
      }
    }
    return $constraints;
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
