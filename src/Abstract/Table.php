<?php

namespace Ilias\Maestro\Abstract;

use Exception;
use Ilias\Maestro\Core\Maestro;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Database\Transaction;
use Ilias\Maestro\Database\Update;
use Ilias\Maestro\Types\Timestamp;
use Ilias\Maestro\Utils\Utils;
use InvalidArgumentException;
use stdClass;
use Blueprint;

abstract class Table extends stdClass
{
  use Sanitizable;

  public function __construct(...$params)
  {
    foreach ($params as $key => $value) {
      $this->{$key} = $value;
    }
  }

  public function __set($name, $value)
  {
    if (property_exists(static::class, $name)) {
      $this->{$name} = $this->bindValue($name, $value);
    }
  }

  public function __get($name)
  {
    if (property_exists(static::class, $name)) {
      return $this->{$name};
    }
  }

  public function __toString()
  {
    return $this->getTableSchemaAddress();
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
      if (isset($this->{$column})) {
        $values[$columnName] = $this->{$column};
      }
    }
    try {
      $tableIdentifier = static::tableIdentifiers(false);
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

  private function bindValue(string $name, mixed $value): mixed
  {
    if (gettype($this->{$name}) === Timestamp::class) {
      return new Timestamp($value);
    }
    return $value;
  }

  public static function tableName(): string
  {
    return self::sanitizedName();
  }

  public static function getTableSchemaAddress(): string
  {
    return static::tableFullAddress();
  }

  private static function getUniqueColumns(): array
  {
    return self::tableColumnsProperties(Maestro::DOC_UNIQUE);
  }

  /**
   * Get not null properties of a class.
   * @param \ReflectionClass $reflectionClass
   * @return array
   */
  private static function getNotNullProperties(\ReflectionClass $reflectionClass): array
  {
    $properties = [];
    try {
      $constructor = $reflectionClass->getMethod('compose');
    } catch (\Throwable) {
      $constructor = $reflectionClass->getConstructor();
    }
    if ($constructor) {
      $params = $constructor->getParameters();
      foreach ($params as $param) {
        if (!$param->isOptional()) {
          $properties[] = $param->getName();
        }
      }
    }
    foreach ($reflectionClass->name::tableColumnsProperties(Maestro::DOC_NOT_NUABLE) as $value) {
      if (!in_array($value, $properties)) {
        $properties[] = $value;
      }
    }
    return $properties;
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

  public static function tableColumnsProperties(string $atDocClause = ''): array
  {
    $reflection = new \ReflectionClass(static::class);
    $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
    $columns = [];
    foreach ($properties as $property) {
      $docComment = $property->getDocComment();
      if ($docComment && strpos($docComment, $atDocClause) !== false) {
        $columns[] = $property->getName();
      }
    }
    return $columns;
  }

  final public static function tableIdentifiers(bool $snakeCase = true): array
  {
    $identifiers = [];
    foreach (static::tableColumns() as $name => $type) {
      $sanitizedName = Utils::toSnakeCase($name);
      if (is_array($type)) {
        if (is_subclass_of($type[0], Identifier::class)) {
          $identifiers[$snakeCase ? $sanitizedName : $name] = "{$type[0]}";
        }
      }
      if (is_subclass_of($type, Identifier::class)) {
        $identifiers[$snakeCase ? $sanitizedName : $name] = "{$type}";
      }
    }
    if (empty($identifiers)) {
      throw new Exception('No identifier found for table ' . static::tableFullAddress());
    }
    return $identifiers;
  }

  final public static function tablePrimary(bool $snakeCase = true): array
  {
    foreach (static::tableIdentifiers($snakeCase) as $name => $column) {
      return [$name => $column];
    }
    return [];
  }

  /**
   * Get the column type.
   * @param mixed $type
   * @return string
   */
  private static function getColumnType(mixed $type): string
  {
    if (is_subclass_of($type, Table::class)) {
      foreach ($type::tableIdentifiers() as $value) {
        return $value::tableIdentifierReferenceType();
      }
    }
    if (is_subclass_of($type, Identifier::class)) {
      return $type::tableIdentifierType();
    }
    if (is_array($type)) {
      return static::getColumnType($type[0]);
    }
    if (is_string($type)) {
      return Utils::getPostgresType($type);
    }
    throw new InvalidArgumentException('Invalid column type provided.');
  }

  /**
   * Get the default value of a property.
   * @param \ReflectionClass $reflectionClass
   * @param string $propertyName
   * @return mixed
   */
  private static function getPropertyDefaultValue(\ReflectionClass $reflectionClass, string $propertyName): mixed
  {
    $property = $reflectionClass->getProperty($propertyName);
    if ($property->isDefault() && $property->isPublic()) {
      $defaultValues = $reflectionClass->getDefaultProperties();
      return $defaultValues[$propertyName] ?? null;
    }
    return null;
  }


  public static function tableCreationInfo(): array
  {
    $columns = [];

    $reflectionClass = new \ReflectionClass(static::class);
    $identifiers = static::tableIdentifiers(false);
    $primaryColumn = static::tablePrimary(false);
    $notNullColumns = static::getNotNullProperties($reflectionClass);
    $uniqueColumns = static::getUniqueColumns();
    foreach (static::tableColumns() as $name => $type) {
      $columns[$name]['name'] = Utils::sanitizeForPostgres($name);
      $columns[$name]['type'] = self::getColumnType($type);

      $defaultValue = self::getPropertyDefaultValue($reflectionClass, $name);
      if ($defaultValue) {
        $columns[$name]['default'] = Utils::formatDefaultExpressionValue($type, $defaultValue);
      }

      $columns[$name]['primary'] = isset($primaryColumn[$name]);
      $columns[$name]['not_null'] = in_array($name, $notNullColumns);
      $columns[$name]['unique'] = in_array($name, $uniqueColumns) || isset($identifiers[$name]);
    }
    return $columns;
  }

  /**
   * Returns an array of objects from the given data.
   * @param array $data
   * @return array
   */
  protected static function composeTable(array $data): array
  {
    $columns = [];
    foreach (static::tableColumns() as $name => $t) {
      $columns[Utils::sanitizeForPostgres($name)] = $name;
    }
    $objects = [];
    foreach ($data as $row) {
      $translatedRow = [];
      foreach ($row as $column => $value) {
        if (isset($columns[$column])) {
          $translatedRow[$columns[$column]] = $value;
        }
      }
      try {
        $reflectionClass = new \ReflectionClass(static::class);
        $constructorParams = array_map(fn($param) => $param->getName(), $reflectionClass->getConstructor()->getParameters());
        $filteredRow = array_filter($translatedRow, fn($key) => in_array($key, $constructorParams), ARRAY_FILTER_USE_KEY);
        $object = new static(...$filteredRow);
        foreach ($translatedRow as $column => $value) {
          if (!is_null($value) && property_exists(static::class, $column)) {
            $object->{$column} = $value;
          }
        }
      } catch (\Throwable) {
        $object = new stdClass();
        foreach ($translatedRow as $column => $value) {
          $object->{$column} = $value;
        }
      }
      $objects[] = $object;
    }
    return $objects;
  }

  /**
   * Fetches all rows from the table based on the given prediction, order, and limit.
   * @param string|array|null $prediction The prediction criteria for the query. Can be a string or an array.
   * @param string|array|null $orderBy The order by criteria for the query. Can be a string or an array.
   * @param int|string $limit The limit for the number of rows to fetch. Default is 100.
   * @return array|static[] The fetched rows as an array.
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
   * @param string|array|null $prediction The prediction criteria for the query. Can be a string or an array.
   * @param string|array|null $orderBy The order by criteria for the query. Can be a string or an array.
   * @return null|array|static|stdClass The fetched row or null if no row is found.
   */
  public static function fetchRow(string|array $prediction = null, string|array $orderBy = null, bool $fetchObj = true): null|array|static|stdClass
  {
    return self::fetchAll($prediction, $orderBy, 1, $fetchObj)[0] ?? null;
  }

  abstract public static function compose(Blueprint $blueprint): Blueprint;
}
