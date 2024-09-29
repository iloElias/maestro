<?php

namespace Ilias\Maestro\Abstract;

use Ilias\Maestro\Database\DatabaseFunction;

abstract class Database extends \stdClass
{
  use Sanitizable;

  private static array $functions = [];

  public static function getDatabaseName(): string
  {
    return static::class;
  }

  public static function getSchemas(): array
  {
    $reflection = new \ReflectionClass(static::class);
    $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
    $schemas = [];
    foreach ($properties as $property) {
      $type = $property->getType();
      if ($type && is_subclass_of($type->getName(), Schema::class)) {
        $schemas[$property->getName()] = $type->getName();
      }
    }
    return $schemas;
  }

  public static function declareFunction(string $name, string $returnType, string $sqlDefinition)
  {
    self::$functions[$name] = new DatabaseFunction($name, $returnType, $sqlDefinition);
  }

  public static function getFunctions(): array
  {
    return self::$functions;
  }

  public static function dumpDatabase()
  {
    $databaseMap = [];
    $schemas = self::getSchemas();
    foreach ($schemas as $schema) {
      $databaseMap[$schema::sanitizedName()] = $schema::dumpSchema();
    }
    return [self::sanitizedName() => $databaseMap];
  }

  public static function prettyPrint()
  {
    $databaseName = self::sanitizedName();
    echo "Database: $databaseName (Class: " . self::getDatabaseName() . ")\n";

    $schemas = self::getSchemas();
    foreach ($schemas as $schemaName => $schemaClass) {
      echo "\tSchema: $schemaName (Class: $schemaClass)\n";

      $tables = $schemaClass::getTables();
      foreach ($tables as $tableName => $tableClass) {
        echo "\t\tTable: $tableName (Class: $tableClass)\n";

        $columns = $tableClass::tableColumns();
        foreach ($columns as $columnName => $columnType) {
          echo "\t\t\t- Column: $columnName (Type: $columnType)\n";
        }
      }
    }
  }
}
