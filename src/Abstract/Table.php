<?php

namespace Ilias\Maestro\Abstract;

use Exception;
use Ilias\Maestro\Core\Maestro;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Database\Transaction;
use Ilias\Maestro\Database\Update;
use Ilias\Maestro\Utils\Utils;

abstract class Table extends \stdClass
{
  use Sanitizable;

  public function __construct(...$params)
  {
    foreach ($params as $key => $value) {
      $this->{$key} = $value;
    }
  }

  /**
   * Saves the current state of the object to the database.
   * This method iterates over the table columns, converts the column names to snake_case, and prepares the values for insertion or update. It then attempts to retrieve the table identifier and constructs an update query for each identifier. The update operation is executed within a transaction to ensure atomicity.
   * @return bool Returns true if the save operation was successful, false otherwise.
   * @throws Exception If the table has no identifier, an exception is thrown indicating that the operation is not available.
   */
  public function save(): bool
  {
    $values = [];
    foreach (static::tableColumns() as $column => $type) {
      $columnName = Utils::toSnakeCase($column);
      $values[$columnName] = $this->{$column};
    }
    try {
      $tableIdentifier = static::tableIdentifier(false);
    } catch (\Throwable) {
      throw new Exception('Table ' . static::tableFullAddress() . ' has no identifier. This operation is not available.');
    }
    foreach ($tableIdentifier as $name => $_) {
      $sanitizedName = Utils::toSnakeCase($name);
      $update = new Update();
      $update->table(static::tableFullAddress())
        ->set($values)
        ->where([$sanitizedName => $this->{$name}]);
      $transaction = new Transaction();
      $transaction->begin();
      try {
        $update->execute();
        $transaction->commit();
        return true;
      } catch (\Throwable) {
        $transaction->rollback();
        return false;
      }
    }
    return false;
  }

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

  final public static function tableIdentifier(bool $snakeCase = true): array
  {
    foreach (static::tableColumns() as $name => $type) {
      $sanitizedName = Utils::toSnakeCase($name);
      if (is_array($type)) {
        if (is_subclass_of($type[0], Identifier::class)) {
          return [$snakeCase ? $sanitizedName : $name => "{$type[0]}"];
        }
      }
      if (is_subclass_of($type, Identifier::class)) {
        return [$snakeCase ? $sanitizedName : $name => "{$type}"];
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
   * Returns an array of objects from the given data.
   * @param array $data
   * @return array
   */
  protected static function composeTable(array $data): array
  {
    $objects = [];
    foreach ($data as $row) {
      try {
        $object = new static(...$row);
        foreach ($row as $column => $value) {
          $object->{$column} = $value;
        }
      } catch (\Throwable) {
        $object = new \stdClass();
        foreach ($row as $column => $value) {
          $object->{$column} = $value;
        }
      }
      $objects[] = $object;
    }
    return $objects;
  }

  /**
   * Fetches all rows from the table based on the given prediction, order, and limit.
   *
   * @param string|array|null $prediction The prediction criteria for the query. Can be a string or an array.
   * @param string|array|null $orderBy The order by criteria for the query. Can be a string or an array.
   * @param int|string $limit The limit for the number of rows to fetch. Default is 100.
   * @return array The fetched rows as an array.
   */
  public static function fetchAll(string|array $prediction = null, string|array $orderBy = null, int|string $limit = 100, bool $fetchObj = true): array
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from([static::getTableSchemaAddress()]);
    if (!empty($prediction)) {
      $select->where($prediction);
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
    $result = $select->bindParameters()->execute();
    $return = $fetchObj ? self::composeTable($result) : $result;
    return $return;
  }

  /**
   * Fetches a single row from the table based on the given prediction and order.
   *
   * @param string|array|null $prediction The prediction criteria for the query. Can be a string or an array.
   * @param string|array|null $orderBy The order by criteria for the query. Can be a string or an array.
   * @return mixed The fetched row or null if no row is found.
   */
  public static function fetchRow(string|array $prediction = null, string|array $orderBy = null, bool $fetchObj = true): null|array|static
  {
    return self::fetchAll($prediction, $orderBy, 1, $fetchObj)[0] ?? null;
  }
}
