<?php

namespace Maestro\Tests\Unit;

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

    $expectedSql = 'CREATE TABLE IF NOT EXISTS "hr"."user" ( "id" SERIAL NOT NULL PRIMARY KEY UNIQUE, "uuid" UUID NOT NULL DEFAULT gen_random_uuid() UNIQUE, "first_name" TEXT NOT NULL, "last_name" TEXT NOT NULL, "nickname" TEXT NOT NULL UNIQUE, "email" TEXT NOT NULL UNIQUE, "password" TEXT NOT NULL, "active" BOOLEAN NOT NULL DEFAULT TRUE, "created_in" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, "updated_in" TIMESTAMP NULL, "inactivated_in" TIMESTAMP NULL );';

    $this->assertEquals(trim(preg_replace('/\s+/', ' ', $expectedSql)), trim(preg_replace('/\s+/', ' ', $createTableSql)));
  }
}
