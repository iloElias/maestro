<?php

namespace Ilias\Maestro\Utils;

use DateTime;

class Utils
{
  private const PHP_TO_POSTGRES_TYPE_MAP = [
    "int" => "integer",
    "integer" => "integer",
    "float" => "real",
    "double" => "double precision",
    "string" => "text",
    "bool" => "boolean",
    "boolean" => "boolean",
    "array" => "json",
    "object" => "json",
    "DateTime" => "timestamp",
    "NULL" => "null",
    "unknown type" => "null",
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
