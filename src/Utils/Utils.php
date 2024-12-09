<?php

namespace Ilias\Maestro\Utils;

use Ilias\Maestro\Abstract\Identifier;
use Ilias\Maestro\Abstract\Query;
use Ilias\Maestro\Database\Expression;
use Ilias\Maestro\Types\Postgres;
use Ilias\Maestro\Types\Serial;
use Ilias\Maestro\Types\Timestamp;
use Ilias\Maestro\Types\Unique;

class Utils
{
    private const PHP_TO_POSTGRES_TYPE = [
      'int'            => Postgres::INTEGER,
      'integer'        => Postgres::INTEGER,
      'float'          => Postgres::NUMERIC,
      'double'         => Postgres::DOUBLE_PRECISION,
      'string'         => Postgres::TEXT,
      'bool'           => Postgres::BOOLEAN,
      'boolean'        => Postgres::BOOLEAN,
      'array'          => Postgres::JSON,
      'object'         => Postgres::JSON,
      Timestamp::class => Postgres::TIMESTAMP,
      Serial::class    => Postgres::SERIAL,
      Unique::class    => Postgres::UUID,
      'unknown_type'   => Postgres::TEXT,
    ];

    public static function getPostgresType(string $phpType)
    {
        return self::PHP_TO_POSTGRES_TYPE[$phpType] ?? self::PHP_TO_POSTGRES_TYPE['unknown_type'];
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

        $sanitized = rtrim($sanitized, '_');

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

    public static function isIdentifier(string|array $columnType): bool
    {
        if (is_array($columnType) && is_subclass_of($columnType[0], Identifier::class)) {
            return true;
        }
        if (is_subclass_of($columnType, Identifier::class)) {
            return true;
        }

        return false;
    }

    /**
     * Format insert value for SQL query.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function formatQueryValue(mixed $value): string
    {
        if (!is_bool($value) && in_array($value, Expression::DEFAULT_REPLACE_EXPRESSIONS)) {
            return Expression::DEFAULT;
        }
        if (is_null($value)) {
            return Expression::NULL;
        } elseif (is_int($value) || is_float($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return $value ? Expression::TRUE : Expression::FALSE;
        } elseif (is_object($value) && is_subclass_of($value, Query::class)) {
            return "({$value})";
        } elseif (enum_exists($value::class)) {
            $value = str_replace("'", "''", $value->value);

            return "'{$value}'";
        } elseif ($value instanceof Expression) {
            return "{$value}";
        } else {
            $value = str_replace("'", "''", $value);

            return "'{$value}'";
        }
    }

    /**
     * Format the default value for SQL table.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function formatDefaultExpressionValue(mixed $types, mixed $value): string
    {
        if (is_array($types)) {
            if (in_array(Expression::class, $types)) {
                return $value;
            }

            return self::formatDefaultExpressionValue($types[0], $value);
        }

        return self::formatQueryValue($value);
    }
}
