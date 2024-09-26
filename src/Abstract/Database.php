<?php

namespace Ilias\Maestro\Abstract;

abstract class Database extends \stdClass
{
  use Sanitizable;
  public static function getDatabaseName(): string
  {
    return static::class;
  }

  public static function getDatabaseSchemas(): array
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

  public static function dumpDatabase()
  {
    $databaseMap = [];
    $schemas = self::getDatabaseSchemas();
    foreach ($schemas as $schema) {
      $databaseMap[$schema::getSanitizedName()] = $schema::dumpSchema();
    }
    return [self::getSanitizedName() => $databaseMap];
  }
}
