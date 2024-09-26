<?php

namespace Ilias\Maestro\Core;

use PDO;
use Ilias\Maestro\Abstract\Database;
use Ilias\Maestro\Database\PDOConnection;
use Ilias\Maestro\Utils\Utils;

class Synchronizer
{
  private Manager $manager;
  private PDO $pdo;
  public function __construct()
  {
    $this->manager = new Manager();
    $this->pdo = PDOConnection::getInstance();
  }

  public function synchronize(Database $ormDb): void
  {
    $ormVector = $this->vectorizeORM($ormDb);
    $dbVector = $this->vectorizeDatabase($this->pdo);
    $differences = $this->compareVectors($ormVector, $dbVector);
    $queries = $this->generateSQLQueries($differences);

    echo var_dump($ormVector, $dbVector, $differences, $queries);
    return;

    // $this->executeQueries($queries, $this->pdo);
  }

  private function vectorizeORM(Database $ormDb): array
  {
    $vector = [];

    foreach ($ormDb::getDatabaseSchemas() as $schemaName => $schema) {
      foreach ($schema::getSchemaTables() as $tableName => $table) {
        $columns = $table::tableColumns();
        foreach ($columns as $columnName => $column) {
          $vector[$schemaName][$tableName][$columnName] = [
            'type' => $column['type'],
            'default' => $column['default'],
            'not_null' => $column['not_null'],
            'is_unique' => $column['is_unique'],
          ];
        }
      }
    }

    return $vector;
  }

  private function vectorizeDatabase(PDO $pdo): array
  {
    $vector = [];

    $query = "SELECT c.table_schema, c.table_name, c.column_name, c.data_type, c.column_default, c.is_nullable, tc.constraint_type
      FROM information_schema.columns c
      LEFT JOIN information_schema.key_column_usage kcu
      ON c.table_schema = kcu.table_schema
      AND c.table_name = kcu.table_name
      AND c.column_name = kcu.column_name
      LEFT JOIN information_schema.table_constraints tc
      ON kcu.constraint_name = tc.constraint_name
      AND kcu.table_schema = tc.table_schema
      AND kcu.table_name = tc.table_name
      AND tc.constraint_type = 'UNIQUE'
      WHERE c.table_schema NOT IN ('information_schema', 'pg_catalog')
      ORDER BY c.table_schema, c.table_name, c.ordinal_position;";
    $stmt = $pdo->query($query);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $schemaName = $row['table_schema'];
      $tableName = $row['table_name'];
      $columnName = $row['column_name'];

      $vector[$schemaName][$tableName][$columnName] = [
        'type' => $row['data_type'],
        'default' => $row['column_default'],
        'not_null' => $row['is_nullable'] === 'NO',
        'is_unique' => $row['constraint_type'] === 'UNIQUE',
      ];
    }

    return $vector;
  }

  private function compareVectors(array $ormVector, array $dbVector): array
  {
    $differences = [];

    foreach ($ormVector as $schemaName => $schema) {
      foreach ($schema as $tableName => $table) {
        if (!isset($dbVector[$schemaName][$tableName])) {
          $differences[] = [
            'action' => 'create_table',
            'table' => $tableName,
          ];
          continue;
        }

        $dbTable = $dbVector[$schemaName][$tableName];
        foreach ($table as $columnName => $column) {
          if (!isset($dbTable[$columnName])) {
            $differences[] = [
              'action' => 'add_column',
              'schema' => $schemaName,
              'table' => $tableName,
              'column' => $columnName,
              'definition' => $column
            ];
          }
        }
      }
    }

    return $differences;
  }

  private function generateSQLQueries(array $differences): array
  {
    $queries = [];

    foreach ($differences as $difference) {
      switch ($difference['action']) {
        case 'create_table':
          $queries[] = $this->manager->createTable($difference['table']);
          break;
        case 'add_column':
          $queries[] = $this->generateAddColumnSQL(
            $difference['schema'],
            $difference['table'],
            $difference['column'],
            $difference['definition']
          );
          break;
      }
    }

    return $queries;
  }

  private function generateAddColumnSQL(string $schema, string $table, string $column, array $definition): string
  {
    $type = Utils::getPostgresType(is_array($definition['type']) ? $definition['type'][0] : $definition['type']);
    $notNull = $definition['not_null'] ? 'NOT NULL' : 'NULL';
    $default = $definition['default'] ? "DEFAULT {$definition['default']}" : '';

    return "ALTER TABLE \"$schema\".\"$table\" ADD COLUMN \"$column\" $type $notNull $default;";
  }

  private function executeQueries(array $queries, PDO $pdo): void
  {
    foreach ($queries as $query) {
      $this->manager->executeQuery($pdo, $query);
    }
  }
}
