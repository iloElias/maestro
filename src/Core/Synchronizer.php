<?php

namespace Ilias\Maestro\Core;

use Ilias\Maestro\Abstract\Database;
use Ilias\Maestro\Abstract\Schema;
use Ilias\Maestro\Helpers\SchemaComparator;
use PDO;

class Synchronizer
{
  private SchemaComparator $schemaComparator;
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->schemaComparator = new SchemaComparator();
    $this->pdo = $pdo;
  }

  public function synchronizeDatabase(Database $database): array
  {
    $schemas = $database::getSchemas();
    $sqlStatements = [];

    foreach ($schemas as $schemaClass) {
      $schema = new $schemaClass();
      $schemaSqlStatements = $this->synchronizeSchema($schema);
      $sqlStatements = array_merge($sqlStatements, $schemaSqlStatements);
    }

    return $sqlStatements;
  }

  public function synchronizeSchema(Schema $schema): array
  {
    $schemaName = $schema::getSanitizedName();
    $this->createSchemaIfNotExists($schemaName);

    $dbSchema = $this->getDatabaseSchema($schemaName);
    $definedSchema = $this->schemaComparator->getDefinedSchema($schema);

    // echo "DB Schema:\n";
    // print_r($dbSchema);
    // echo "Defined Schema:\n";
    // print_r($definedSchema);

    $differences = $this->schemaComparator->compareSchemas($dbSchema, $definedSchema);

    // echo "Differences:\n";
    // print_r($differences);

    $sqlStatements = $this->generateSqlStatements($differences, $schema);
    $this->applySqlStatements($sqlStatements);

    return $sqlStatements;
  }

  private function createSchemaIfNotExists(string $schemaName)
  {
    $sql = "CREATE SCHEMA IF NOT EXISTS \"$schemaName\";";
    $this->pdo->exec($sql);
  }

  private function generateSqlStatements(array $differences, Schema $schema): array
  {
    $sqlStatements = [];
    $schemaName = $schema::getSanitizedName();
    $tables = $schema::getTables();

    if (isset($differences['create'])) {
      foreach ($differences['create'] as $tableName) {
        if (isset($tables[$tableName])) {
          $sqlStatements[] = $this->createTable($tables[$tableName]);
        }
      }
    }

    if (isset($differences['add'])) {
      foreach ($differences['add'] as $tableName => $columns) {
        foreach ($columns as $column) {
          $columnType = $column['data_type'];
          $sqlStatements[] = "ALTER TABLE \"$schemaName\".\"$tableName\" ADD COLUMN \"{$column['column_name']}\" $columnType;";
        }
      }
    }

    if (isset($differences['remove'])) {
      foreach ($differences['remove'] as $tableName => $columns) {
        foreach ($columns as $column) {
          $sqlStatements[] = "ALTER TABLE \"$schemaName\".\"$tableName\" DROP COLUMN \"$column\";";
        }
      }
    }

    if (isset($differences['drop'])) {
      foreach ($differences['drop'] as $tableName) {
        $sqlStatements[] = "DROP TABLE \"$schemaName\".\"$tableName\";";
      }
    }

    // echo "Generated SQL Statements:\n";
    // print_r($sqlStatements);

    return $sqlStatements;
  }

  private function createTable(string $tableClass): string
  {
    $tableName = $tableClass::getSanitizedName();
    $schemaName = $this->getSchemaNameFromTable($tableClass);
    $columns = $tableClass::getColumns();
    $primaryKey = 'id';
    $columnDefs = [];

    foreach ($columns as $name => $type) {
      $columnDef = "\"$name\" " . $this->getColumnType($type);
      if ($name === $primaryKey) {
        $columnDef .= " PRIMARY KEY";
      }
      $columnDefs[] = $columnDef;
    }

    $columnsSql = implode(", ", $columnDefs);
    return "CREATE TABLE \"$schemaName\".\"$tableName\" ($columnsSql);";
  }

  private function getColumnType(string $type): string
  {
    return $this->schemaComparator->mapDataType($type);
  }

  private function getSchemaNameFromTable(string $tableClass): string
  {
    $reflectionClass = new \ReflectionClass($tableClass);
    $schemaProperty = $reflectionClass->getProperty('schema');
    $schemaClass = $schemaProperty->getType()->getName();
    return (new \ReflectionClass($schemaClass))->getShortName();
  }

  private function applySqlStatements(array $sqlStatements)
  {
    foreach ($sqlStatements as $sql) {
      $this->pdo->exec($sql);
    }
  }

  private function getDatabaseSchema(string $schemaName): array
  {
    $query = $this->pdo->query("SELECT table_name, column_name, data_type FROM information_schema.columns WHERE table_schema = '$schemaName'");
    $dbSchema = [];

    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
      $dbSchema[$row['table_name']][$row['column_name']] = [
        'column_name' => $row['column_name'],
        'data_type' => $row['data_type']
      ];
    }

    // print_r($dbSchema);

    return $dbSchema;
  }
}
