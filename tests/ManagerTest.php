<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ilias\Maestro\Core\Manager;
use Ilias\Maestro\Database\PDOConnection;
use Maestro\Example\Hr;

class ManagerTest extends TestCase
{
  private $pdoMock;
  private $manager;

  protected function setUp(): void
  {
    $this->pdoMock = $this->createMock(\PDO::class);
    PDOConnection::getInstance($this->pdoMock);
    $this->manager = new Manager();
  }

  public function testCreateTable()
  {
    $tableClass = Hr::getTables()['user'];
    $createTableSql = $this->manager->createTable($tableClass);

    $expectedSql = 'CREATE TABLE IF NOT EXISTS "hr"."user" (
        id SERIAL PRIMARY KEY,
        nickname TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        active BOOLEAN NOT NULL DEFAULT TRUE,
        created_in TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_in TIMESTAMP NULL,
        inactivated_in TIMESTAMP NULL
      );';

    $this->assertEquals(trim(preg_replace('/\s+/', ' ', $expectedSql)), trim(preg_replace('/\s+/', ' ', $createTableSql)));
  }

  public function testAddColumnToTable()
  {
    $schema = new Hr();

    $dbSchema = [
      'user' => [
        ['column_name' => 'id', 'data_type' => 'integer'],
        ['column_name' => 'nickname', 'data_type' => 'text'],
      ],
    ];

    $definedSchema = $this->manager->getSchemaComparator()->getDefinedSchema($schema);
    $differences = $this->manager->getSchemaComparator()->compareSchemas($dbSchema, $definedSchema);

    $sqlStatements = $this->manager->generateSqlStatements($differences, $schema);

    $expectedSql = 'ALTER TABLE "hr"."user" ADD COLUMN email TEXT;';
    $this->assertContains($expectedSql, $sqlStatements);
  }

  public function testRemoveColumnFromTable()
  {
    $schema = new Hr();

    $dbSchema = [
      'user' => [
        ['column_name' => 'id', 'data_type' => 'integer'],
        ['column_name' => 'nickname', 'data_type' => 'text'],
        ['column_name' => 'unused_column', 'data_type' => 'text'],
      ],
    ];

    $definedSchema = $this->manager->getSchemaComparator()->getDefinedSchema($schema);
    $differences = $this->manager->getSchemaComparator()->compareSchemas($dbSchema, $definedSchema);

    $sqlStatements = $this->manager->generateSqlStatements($differences, $schema);

    $expectedSql = 'ALTER TABLE "hr"."user" DROP COLUMN unused_column;';
    $this->assertContains($expectedSql, $sqlStatements);
  }

  public function testDropTable()
  {
    $schema = new Hr();

    $dbSchema = [
      'user' => [
        ['column_name' => 'id', 'data_type' => 'integer'],
        ['column_name' => 'nickname', 'data_type' => 'text'],
      ],
      'old_table' => [
        ['column_name' => 'id', 'data_type' => 'integer'],
      ],
    ];

    $definedSchema = $this->manager->getSchemaComparator()->getDefinedSchema($schema);
    $differences = $this->manager->getSchemaComparator()->compareSchemas($dbSchema, $definedSchema);

    $sqlStatements = $this->manager->generateSqlStatements($differences, $schema);

    $expectedSql = 'DROP TABLE "hr"."old_table";';
    $this->assertContains($expectedSql, $sqlStatements);
  }
}
