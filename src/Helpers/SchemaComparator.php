<?php

namespace Ilias\Maestro\Helpers;

use Ilias\Maestro\Abstract\Schema;
use Ilias\Maestro\Database\PDOConnection;

class SchemaComparator
{
  private $pdo;

  public function __construct()
  {
    $this->pdo = PDOConnection::getInstance();
  }

  public function getDatabaseSchema(string $schemaName): array
  {
    $query = "SELECT table_name, column_name, data_type 
              FROM information_schema.columns 
              WHERE table_schema = :schema_name";
    $stmt = $this->pdo->prepare($query);
    $stmt->execute(['schema_name' => $schemaName]);

    $schema = [];
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
      $schema[$row['table_name']][] = [
        'column_name' => $row['column_name'],
        'data_type' => $row['data_type']
      ];
    }

    return $schema;
  }

  public function getDefinedSchema(Schema $schema): array
  {
    $definedSchema = [];
    $tables = $schema::getTables();

    foreach ($tables as $tableClass) {
      $tableName = $tableClass::getTableName();
      $columns = $tableClass::getColumns();
      foreach ($columns as $columnName => $columnType) {
        $definedSchema[$tableName][] = [
          'column_name' => $columnName,
          'data_type' => $this->getColumnType($columnType)
        ];
      }
    }

    return $definedSchema;
  }

  private function getColumnType(string $type): string
  {
    // Map PHP types to database types
    $typeMap = [
      'int' => 'integer',
      'string' => 'text',
      'bool' => 'boolean',
      'DateTime' => 'timestamp'
    ];

    return $typeMap[$type] ?? $type;
  }

  public function compareSchemas(array $dbSchema, array $definedSchema): array
  {
    $differences = [];

    foreach ($definedSchema as $tableName => $columns) {
      if (!isset($dbSchema[$tableName])) {
        $differences['create'][] = $tableName;
      } else {
        foreach ($columns as $column) {
          $dbColumns = array_column($dbSchema[$tableName], 'column_name');
          if (!in_array($column['column_name'], $dbColumns)) {
            $differences['add'][$tableName][] = $column;
          }
        }
      }
    }

    foreach ($dbSchema as $tableName => $columns) {
      if (!isset($definedSchema[$tableName])) {
        $differences['drop'][] = $tableName;
      } else {
        foreach ($columns as $column) {
          $definedColumns = array_column($definedSchema[$tableName], 'column_name');
          if (!in_array($column['column_name'], $definedColumns)) {
            $differences['remove'][$tableName][] = $column['column_name'];
          }
        }
      }
    }

    return $differences;
  }
}
