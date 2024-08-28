<?php

namespace Ilias\Maestro\Abstract;

abstract class Table extends \stdClass
{
  use Sanitizable;

  public static function getTableName(): string
  {
    return self::getSanitizedName();
  }

  public function __toString()
  {
    return $this->getTableSchemaAddress();
  }
  public static function getTableSchemaAddress(): string
  {
    $reflection = new \ReflectionClass(static::class);
    $tableReflection = $reflection->getProperty("schema") ?? $reflection->getProperty("Schema");
    $tableSchema = $tableReflection->getType()->getName()::getSanitizedName() ?? "public";
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
        $isUnique = false;

        if ($propertyType instanceof \ReflectionUnionType) {
          $types = $propertyType->getTypes();
          foreach ($types as $type) {
            $columnType[] = $type->getName();
          }
        } else {
          $columnType = $propertyType->getName();
        }

        $docComment = $property->getDocComment();
        if ($docComment && strpos($docComment, '@unique') !== false) {
          $isUnique = true;
        }

        $columns[$property->getName()] = [
          'type' => $columnType,
          'default' => $defaultValue,
          'not_null' => !$isNullable,
          'is_unique' => $isUnique,
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
