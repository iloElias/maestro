<?php

namespace Ilias\Maestro\Abstract;

abstract class Table extends Sanitizable
{
  public int $id;

  public static function getTableName(): string
  {
    return static::class;
  }

  public static function getTableSchemaAddress(): string
  {
    $reflection = new \ReflectionClass(static::class);
    $tableSchema = $reflection->getProperty("schema");
    $tableName = self::getSanitizedName();
    return "\"{$tableSchema}\".\"{$tableName}\"";
  }

  public static function getColumns(): array
  {
    $reflection = new \ReflectionClass(static::class);
    $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
    $columns = [];

    foreach ($properties as $property) {
      if ($property->getName() !== 'schema') {
        try {
          $columns[$property->getName()] = $property->getType()->getName();
        } catch (\Throwable) {
          foreach ($property->getType()->getTypes() as $type) {
            $columns[$property->getName()][] = (string)$type;
          }
        }
      }
    }

    return $columns;
  }

  public static function dumpTable(): array
  {
    return self::getColumns();
  }

  public static function prettyPrint()
  {
    $columns = self::getColumns();
    foreach ($columns as $columnName => $columnType) {
      echo "\t\t\t- Column: $columnName (Type: $columnType)\n";
    }
  }

  public static function getUniqueColumns(): array
  {
    return [];
  }
}
