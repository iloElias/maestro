<?php

namespace Maestro\Tests\Integration;

use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Core\Maestro;
use Ilias\Maestro\Core\Manager;
use Ilias\Maestro\Database\Delete;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\PDOConnection;
use Ilias\Maestro\Database\Update;
use Maestro\Example\Hr;
use Maestro\Example\User;
use PHPUnit\Framework\TestCase;
use PDO;

class IntegrationTest extends TestCase
{
  private PDO $pdo;

  protected function setUp(): void
  {
    $this->pdo = PDOConnection::getInstance();
  }

  public function testCreateSchema()
  {
    $coreDatabase = new Manager();
    $coreDatabase->executeQuery($this->pdo,
      $coreDatabase->createSchema(Hr::class)
    );
  }

  public function testCreateTable()
  {
    $coreDatabase = new Manager();
    $coreDatabase->executeQuery($this->pdo,
      $coreDatabase->createTable(User::class)
    );
  }

  public function testSelectIntegration()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT, $this->pdo);
    $select->from([User::class], ['id', 'email'])->where(['email' => 'email@example.com']);

    $stmt = $this->pdo->prepare($select->getSql());
    $stmt->execute($select->getParameters());
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $this->assertNotEmpty($result);
    $this->assertEquals('email@example.com', $result[0]['email']);
  }

  public function testInsertIntegration()
  {
    $insert = new Insert(Maestro::SQL_NO_PREDICT, $this->pdo);
    $data = ['nickname' => 'nickname', 'email' => 'email@example.com', 'password' => 'password'];
    $insert->into(User::class)->values($data);

    $stmt = $this->pdo->prepare($insert->getSql());
    $stmt->execute($insert->getParameters());

    $this->assertEquals(1, $stmt->rowCount());
  }

  public function testUpdateIntegration()
  {
    $update = new Update(Maestro::SQL_NO_PREDICT, $this->pdo);
    $data = ['nickname' => 'updated_nickname'];
    $conditions = ['email' => 'email@example.com'];
    $update->table(User::class)->set('nickname', 'updated_nickname')->where($conditions);

    $stmt = $this->pdo->prepare($update->getSql());
    $stmt->execute($update->getParameters());

    $this->assertEquals(1, $stmt->rowCount());
  }

  public function testDeleteIntegration()
  {
    $delete = new Delete(Maestro::SQL_NO_PREDICT, $this->pdo);
    $conditions = ['email' => 'email@example.com'];
    $delete->from(User::class)->where($conditions);

    $stmt = $this->pdo->prepare($delete->getSql());
    $stmt->execute($delete->getParameters());

    $this->assertEquals(1, $stmt->rowCount());
  }
}