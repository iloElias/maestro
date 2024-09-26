<?php

namespace Ilias\Maestro\Abstract;

use Ilias\Maestro\Utils\Utils;

abstract class Schema extends \stdClass
{
  use Sanitizable;
  public static function schemaName(): string
  {
    return static::class;
  }

  public static function getSchemaTables(): array
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

  public static function dumpSchema(): array
  {
    $tablesMap = [];
    $tables = self::getSchemaTables();
    foreach ($tables as $table) {
      $tablesMap[$table::getSanitizedName()] = $table::tableColumns();
    }
    return $tablesMap;
  }
}
