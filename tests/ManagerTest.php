<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ilias\Maestro\Core\Manager;
use Ilias\Maestro\Database\PDOConnection;
use Maestro\Example\Hr;

class ManagerTest extends TestCase
{
  private $pdoMock;
  private Manager $manager;

  protected function setUp(): void
  {
    $this->pdoMock = $this->createMock(\PDO::class);
    PDOConnection::getInstance();
    $this->manager = new Manager();
  }

  public function testSynchronizeSchema()
  {
    $schema = new Hr();

    $this->pdoMock->expects($this->once())
      ->method('exec')
      ->with($this->stringContains('CREATE TABLE IF NOT EXISTS'));

    $sqlStatements = $this->manager->synchronizeSchema($schema);

    $this->assertNotEmpty($sqlStatements);
    $this->assertStringContainsString('CREATE TABLE', $sqlStatements[0]);
  }
}
