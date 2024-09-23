<?php

namespace Ilias\Maestro\Utils;

use Timestamp;
use Ilias\Maestro\Types\Timestamp as TypesTimestamp;

class Utils
{
  private const PHP_TO_POSTGRES_TYPE_MAP = [
    "int" => "INTEGER",
    "integer" => "INTEGER",
    "float" => "NUMERIC",
    "double" => "DOUBLE PRECISION",
    "string" => "TEXT",
    "bool" => "BOOLEAN",
    "boolean" => "BOOLEAN",
    "array" => "JSON",
    "object" => "JSON",
    TypesTimestamp::class => "TIMESTAMP",
    "unknown type" => "text",
  ];

  public static function getPostgresType(string $phpType)
  {
    return self::PHP_TO_POSTGRES_TYPE_MAP[$phpType] ?? self::PHP_TO_POSTGRES_TYPE_MAP['unknown type'];
  }

  public static function toSnakeCase(string $text)
  {
    $snake = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $text));
    return strtolower(preg_replace('/([A-Z])([A-Z])([a-z])/', '$1_$2$3', $snake));
  }

  public static function sanitizeForPostgres(string $text)
  {
    $sanitized = preg_replace('/[^a-z0-9_]/', '_', self::toSnakeCase($text));

    if (!preg_match('/^[a-z]/', $sanitized)) {
      $sanitized = '_' . $sanitized;
    }

    return $sanitized;
  }

  public static function getVarType(mixed $var)
  {
    return is_object($var) ? get_class($var) : gettype($var);
  }

  public static function isFinalClass(string $className)
  {
    $reflect = new \ReflectionClass($className);
    return $reflect->isFinal();
  }
}
