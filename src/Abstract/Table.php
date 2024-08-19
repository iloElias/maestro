<?php

namespace Ilias\Maestro\Abstract;

use Ilias\Maestro\Interface\PostgresFunction;

abstract class Table extends Sanitizable
{
  public int $id;

  public static function getTableName(): string
  {
    return self::getSanitizedName();
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
        $propertyType = $property->getType();
        $defaultValue = $property->getDefaultValue();
        $isNullable = $propertyType->allowsNull();
        $columnType = null;

        if ($propertyType instanceof \ReflectionUnionType) {
          $types = $propertyType->getTypes();
          foreach ($types as $type) {
            $columnType[] = $type->getName();
          }
        } else {
          $columnType = $propertyType->getName();
        }

        $columns[$property->getName()] = [
          'type' => $columnType,
          'default' => $defaultValue,
          'nullable' => $isNullable
        ];
      }
    }

    return $columns;
  }

  public static function getTableCreationInfo(): array
  {
    return [
      'tableName' => static::getSanitizedName(),
      'columns' => static::getColumns()
    ];
  }

  public static function getSanitizedName(): string
  {
    $reflect = new \ReflectionClass(static::class);
    return strtolower($reflect->getShortName());
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
