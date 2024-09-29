<?php

namespace Ilias\Maestro\Abstract;

use Ilias\Maestro\Database\DatabaseFunction;
use Ilias\Maestro\Utils\Utils;

abstract class Schema extends \stdClass
{
  use Sanitizable;
  private static array $functions = [];
  public static function getSchemaName(): string
  {
    return static::class;
  }

  public static function getTables(): array
  {
    $reflection = new \ReflectionClass(static::class);
    $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
    $tables = [];
    foreach ($properties as $property) {
      $type = $property->getType();
      if ($type && is_subclass_of($type->getName(), Table::class)) {
        $sanitizedName = Utils::sanitizeForPostgres($property->getName());
        $tables[$sanitizedName] = $type->getName();
      }
    }
    return $tables;
  }

  public static function declareFunction(string $name, string $returnType, string $sqlDefinition)
  {
    self::$functions[$name] = new DatabaseFunction($name, $returnType, $sqlDefinition);
  }

  public static function getFunctions(): array
  {
    return self::$functions;
  }

  public static function dumpSchema(): array
  {
    $tablesMap = [];
    $tables = self::getTables();
    foreach ($tables as $table) {
      $tablesMap[$table::tableSanitizedName()] = $table::dumpTable();
    }
    return $tablesMap;
  }

  public static function prettyPrint()
  {
    $tables = self::getTables();
    foreach ($tables as $tableName => $tableClass) {
      echo "\t\tTable: $tableName (Class: $tableClass)\n";

      $columns = $tableClass::tableColumns();
      foreach ($columns as $columnName => $columnType) {
        echo "\t\t\t- Column: $columnName (Type: $columnType)\n";
      }
    }
  }
}
