<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ilias\Maestro\Database\Insert;
use Maestro\Example\User;

class InsertTest extends TestCase
{
  public function testInsert()
  {
    $insert = new Insert();
    $table = User::class;
    $data = ['nickname' => 'nickname', 'email' => 'email@example.com', 'password' => 'password'];

    $insert->into($table::getTableName())->values($data);

    $expectedSql = "INSERT INTO user (nickname, email, password) VALUES (:nickname, :email, :password)";
    $expectedParams = [':nickname' => 'nickname', ':email' => 'email@example.com', ':password' => 'password'];

    $this->assertEquals($expectedSql, $insert->getSql());
    $this->assertEquals($expectedParams, $insert->getParameters());
  }

  public function testInsertWithMissingFields()
  {
    $insert = new Insert();
    $table = User::class;
    $data = ['nickname' => 'nickname', 'email' => 'email@example.com'];

    $insert->into($table::getTableName())->values($data);

    $expectedSql = "INSERT INTO user (nickname, email) VALUES (:nickname, :email)";
    $expectedParams = [':nickname' => 'nickname', ':email' => 'email@example.com'];

    $this->assertEquals($expectedSql, $insert->getSql());
    $this->assertEquals($expectedParams, $insert->getParameters());
  }

  public function testInsertWithAllFields()
  {
    $insert = new Insert();
    $table = User::class;
    $data = [
      'nickname' => 'nickname',
      'email' => 'email@example.com',
      'password' => 'password',
      'active' => true,
      'createdIn' => 'CURRENT_TIMESTAMP',
      'updatedIn' => '2022-01-01 00:00:00',
      'inactivatedIn' => null
    ];

    $insert->into($table::getTableName())->values($data);

    $expectedSql = "INSERT INTO user (nickname, email, password, active, createdIn, updatedIn, inactivatedIn) VALUES (:nickname, :email, :password, :active, :createdIn, :updatedIn, :inactivatedIn)";
    $expectedParams = [
      ':nickname' => 'nickname',
      ':email' => 'email@example.com',
      ':password' => 'password',
      ':active' => true,
      ':createdIn' => 'CURRENT_TIMESTAMP',
      ':updatedIn' => '2022-01-01 00:00:00',
      ':inactivatedIn' => null
    ];

    $this->assertEquals($expectedSql, $insert->getSql());
    $this->assertEquals($expectedParams, $insert->getParameters());
  }

  public function testInsertWithDateTime()
  {
    $insert = new Insert();
    $table = User::class;
    $date = new \DateTime();
    $data = ['nickname' => 'nickname', 'email' => 'email@example.com', 'created_at' => $date];

    $insert->into($table::getTableName())->values($data);

    $expectedSql = "INSERT INTO user (nickname, email, created_at) VALUES (:nickname, :email, :created_at)";
    $expectedParams = [':nickname' => 'nickname', ':email' => 'email@example.com', ':created_at' => $date];

    $this->assertEquals($expectedSql, $insert->getSql());
    $this->assertEquals($expectedParams, $insert->getParameters());
  }

  public function testInsertWithNullValue()
  {
    $insert = new Insert();
    $table = User::class;
    $data = ['nickname' => 'nickname', 'email' => null, 'password' => 'password'];

    $insert->into($table::getTableName())->values($data);

    $expectedSql = "INSERT INTO user (nickname, email, password) VALUES (:nickname, :email, :password)";
    $expectedParams = [':nickname' => 'nickname', ':email' => null, ':password' => 'password'];

    $this->assertEquals($expectedSql, $insert->getSql());
    $this->assertEquals($expectedParams, $insert->getParameters());
  }
}
