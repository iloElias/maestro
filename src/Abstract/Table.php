<?php

namespace Ilias\Maestro\Abstract;

use Exception;
use Ilias\Maestro\Core\Maestro;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Utils\Utils;

abstract class Table extends \stdClass
{
  use Sanitizable;

  public static function tableName(): string
  {
    return self::sanitizedName();
  }

  public function __toString()
  {
    return $this->getTableSchemaAddress();
  }

  public static function getTableSchemaAddress(): string
  {
    $reflection = new \ReflectionClass(static::class);
    $schemaNamespace = explode('\\', $reflection->getProperty("schema")->getType()->getName());
    $tableSchema = Utils::sanitizeForPostgres($schemaNamespace[array_key_last($schemaNamespace)]);
    $tableName = self::sanitizedName();
    return "\"{$tableSchema}\".\"{$tableName}\"";
  }

  final public static function tableFullAddress(): string
  {
    $reflection = new \ReflectionClass(static::class);
    $schemaNamespace = explode('\\', $reflection->getProperty("schema")->getType()->getName());
    $tableSchema = Utils::sanitizeForPostgres($schemaNamespace[array_key_last($schemaNamespace)]);
    $tableName = self::sanitizedName();
    return "\"{$tableSchema}\".\"{$tableName}\"";
  }

  public static function tableColumns(): array
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

  public static function getUniqueColumns(): array
  {
    return self::tableColumnsProperties(Maestro::DOC_UNIQUE);
  }

  public static function tableColumnsProperties(string $atDocClause = ''): array
  {
    $reflection = new \ReflectionClass(static::class);
    $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
    $uniqueColumns = [];

    foreach ($properties as $property) {
      $docComment = $property->getDocComment();
      if ($docComment && strpos($docComment, $atDocClause) !== false) {
        $uniqueColumns[] = $property->getName();
      }
    }

    return $uniqueColumns;
  }

  final public static function tableIdentifier(): array
  {
    foreach (static::tableColumns() as $name => $type) {
      if (is_array($type)) {
        if (is_subclass_of($type[0], Identifier::class)) {
          return [Utils::toSnakeCase($name) => "{$type[0]}"];
        }
      }
      if (is_subclass_of($type, Identifier::class)) {
        return [Utils::toSnakeCase($name) => "{$type}"];
      }
    }
    throw new Exception('No identifier found for table ' . static::tableFullAddress());
  }

  public static function tableCreationInfo(): array
  {
    return [
      'tableName' => static::sanitizedName(),
      'columns' => static::tableColumns()
    ];
  }

  public static function dumpTable(): array
  {
    return self::tableColumns();
  }

  public static function prettyPrint()
  {
    $columns = self::tableColumns();
    foreach ($columns as $columnName => $columnType) {
      echo "\t\t\t- Column: $columnName (Type: $columnType)\n";
    }
  }

  public static function tableUniqueColumns(): array
  {
    $reflection = new \ReflectionClass(static::class);
    $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
    $uniqueColumns = [];

    foreach ($properties as $property) {
      $docComment = $property->getDocComment();
      if ($docComment && strpos($docComment, '@unique') !== false) {
        $uniqueColumns[] = $property->getName();
      }
    }

    return $uniqueColumns;
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

  /**
   * Fetches all rows from the table based on the given prediction, order, and limit.
   *
   * @param string|array|null $prediction The prediction criteria for the query. Can be a string or an array.
   * @param string|array|null $orderBy The order by criteria for the query. Can be a string or an array.
   * @param int|string $limit The limit for the number of rows to fetch. Default is 100.
   * @return array The fetched rows as an array.
   */
  public static function fetchAll(string|array $prediction = null, string|array $orderBy = null, int|string $limit = 100): array
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
    return $select->bindParameters()->execute();
  }

  /**
   * Fetches a single row from the table based on the given prediction and order.
   *
   * @param string|array|null $prediction The prediction criteria for the query. Can be a string or an array.
   * @param string|array|null $orderBy The order by criteria for the query. Can be a string or an array.
   * @return mixed The fetched row or null if no row is found.
   */
  public static function fetchRow(string|array $prediction = null, string|array $orderBy = null)
  {
    return self::fetchAll($prediction, $orderBy, 1)[0] ?? null;
  }
}
