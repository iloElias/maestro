<?php

namespace Ilias\Maestro\Abstract;
use Ilias\Maestro\Database\Select;

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
        try {
          $columns[$property->getName()] = $property->getType()->getName();
        } catch (\Throwable) {
          foreach ($property->getType()->getTypes() as $type) {
            $columns[$property->getName()][] = (string) $type;
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

  public static function generateAlias(array $existingAlias = []): string
  {
    $baseAlias = strtolower((new \ReflectionClass(static::class))->getShortName());
    $alias = $baseAlias;
    $counter = 1;

    while (in_array($alias, $existingAlias)) {
      $alias = $baseAlias . $counter;
      $counter++;
    }

    return $alias;
  }

  public static function fetchAll(string|array $prediction = null, string|array $orderBy = null, int|string $limit = 100)
  {
    $select = new Select();
    $select->from([static::generateAlias() => static::getTableSchemaAddress()]);
    if (!empty($prediction)) {
      if (is_array($prediction)) {
        $select->where($prediction);
      }
      if (is_string($prediction)) {
        $select->where([$prediction]);
      }
    }
    if (!empty($orderBy)) {
      if (is_array($orderBy)) {
        foreach ($orderBy as $order) {
          $select->order($order);
        }
      }
      if (is_string($orderBy)) {
        $select->order($orderBy);
      }
    }
    $select->limit($limit);
  }
}
