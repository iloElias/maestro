<?php

namespace Ilias\Maestro\Helpers;

use Ilias\Maestro\Abstract\Schema;
use Ilias\Maestro\Database\PDOConnection;
use PDO;

class SchemaComparator
{
  private $pdo;

  public function __construct()
  {
    $this->pdo = PDOConnection::getInstance();
  }

  private function mapDataType(string $type): string
  {
      $typeMapping = [
          'int' => 'integer',
          'integer' => 'integer',
          'string' => 'text',
          'bool' => 'boolean',
          'boolean' => 'boolean',
          'DateTime' => 'timestamp without time zone',
          'Ilias\Maestro\Interface\PostgresFunction' => 'timestamp without time zone'
      ];
  
      if (class_exists($type)) {
          return 'integer';
      }
  
      return $typeMapping[$type] ?? $type;
  }  

  public function getDatabaseSchema(string $schemaName): array
  {
    $stmt = $this->pdo->prepare(
      "SELECT table_name, column_name, data_type
      FROM information_schema.columns
      WHERE table_schema = :schemaName
      ORDER BY table_name, ordinal_position"
    );
    $stmt->execute([':schemaName' => $schemaName]);

    $schema = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $table = $row['table_name'];
      $column = [
        'column_name' => $row['column_name'],
        'data_type' => $row['data_type']
      ];

      if (!isset($schema[$table])) {
        $schema[$table] = [];
      }

      $schema[$table][$row['column_name']] = $column;
    }

    return $schema;
  }

  public function getDefinedSchema(Schema $schema): array
  {
    $definedSchema = [];
    $tables = $schema::getTables();

    foreach ($tables as $tableClass) {
      $tableName = $tableClass::getSanitizedName();
      $columns = $tableClass::getColumns();

      foreach ($columns as $columnName => $columnType) {
        $dataType = is_array($columnType) ? $this->mapDataType($columnType[0]) : $this->mapDataType($columnType);
        $definedSchema[$tableName][$columnName] = [
          'column_name' => $columnName,
          'data_type' => $dataType
        ];
      }
    }

    return $definedSchema;
  }

  public function compareSchemas(array $dbSchema, array $definedSchema): array
  {
    $differences = [
      'create' => [],
      'add' => [],
      'remove' => [],
      'drop' => []
    ];

    foreach ($definedSchema as $tableName => $definedColumns) {
      if (!isset($dbSchema[$tableName])) {
        $differences['create'][] = $tableName;
      }
    }

    foreach ($definedSchema as $tableName => $definedColumns) {
      if (isset($dbSchema[$tableName])) {
        $dbColumns = $dbSchema[$tableName];

        foreach ($definedColumns as $definedColumn) {
          $columnName = $definedColumn['column_name'];
          $dbColumnType = $dbColumns[$columnName]['data_type'] ?? null;

          if (!isset($dbColumns[$columnName])) {
            $differences['add'][$tableName][] = $definedColumn;
          } elseif ($dbColumnType !== $definedColumn['data_type']) {
            $differences['remove'][$tableName][] = $columnName;
            $differences['add'][$tableName][] = $definedColumn;
          }
        }

        foreach ($dbColumns as $dbColumn) {
          $columnName = $dbColumn['column_name'];
          if (!isset($definedColumns[$columnName])) {
            $differences['remove'][$tableName][] = $columnName;
          }
        }
      }
    }

    foreach ($dbSchema as $tableName => $dbColumns) {
      if (!isset($definedSchema[$tableName])) {
        $differences['drop'][] = $tableName;
      }
    }

    return $differences;
  }
}
