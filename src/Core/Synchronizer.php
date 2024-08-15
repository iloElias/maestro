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

    foreach (get_object_vars($ormDb) as $schemaName => $schema) {
      $schemaVector = [];

      foreach (get_object_vars($schema) as $tableName => $table) {
        $tableVector = [
          'columns' => $table::getColumns(),
          'unique' => $table::getUniqueColumns(),
        ];

        $schemaVector[$tableName] = $tableVector;
      }

      $vector[$schemaName] = $schemaVector;
    }

    return $vector;
  }

  private function vectorizeDatabase(PDO $pdo): array
  {
    $vector = [];

    $query = "SELECT table_schema, table_name, column_name, data_type, column_default, is_nullable
      FROM information_schema.columns
      WHERE table_schema NOT IN ('information_schema', 'pg_catalog')
      ORDER BY table_schema, table_name, ordinal_position;";
    $stmt = $pdo->query($query);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $schemaName = $row['table_schema'];
      $tableName = $row['table_name'];
      $columnName = $row['column_name'];

      $vector[$schemaName][$tableName]['columns'][$columnName] = [
        'type' => $row['data_type'],
        'default' => $row['column_default'],
        'not_null' => $row['is_nullable'] === 'NO',
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
        foreach ($table['columns'] as $columnName => $column) {
          if (!isset($dbTable['columns'][$columnName])) {
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
    $type = Utils::getPostgresType($definition['type']);
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
