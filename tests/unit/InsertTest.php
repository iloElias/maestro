<?php

namespace Maestro\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Core\Maestro;
use Ilias\Maestro\Database\Expression;
use Ilias\Maestro\Types\Timestamp;
use Maestro\Example\User;

class InsertTest extends TestCase
{
  public function testInsert()
  {
    $insert = new Insert(Maestro::SQL_NO_PREDICT);
    $table = User::class;
    $data = ['nickname' => 'nickname', 'email' => 'email@example.com', 'password' => 'password'];

    $insert->into($table::tableName())->values($data);

    $expectedSql = "INSERT INTO user (nickname, email, password) VALUES (:nickname, :email, :password)";
    $expectedParams = [':nickname' => "'nickname'", ':email' => "'email@example.com'", ':password' => "'password'"];

    $this->assertEquals($expectedSql, $insert->getSql());
    $this->assertEquals($expectedParams, $insert->getParameters());
  }

  public function testInsertWithMissingFields()
  {
    $insert = new Insert(Maestro::SQL_NO_PREDICT);
    $table = User::class;
    $data = ['nickname' => 'nickname', 'email' => 'email@example.com'];

    $insert->into($table::tableName())->values($data);

    $expectedSql = "INSERT INTO user (nickname, email) VALUES (:nickname, :email)";
    $expectedParams = [':nickname' => "'nickname'", ':email' => "'email@example.com'"];

    $this->assertEquals($expectedSql, $insert->getSql());
    $this->assertEquals($expectedParams, $insert->getParameters());
  }

  public function testInsertWithAllFields()
  {
    $insert = new Insert(Maestro::SQL_NO_PREDICT);
    $table = User::class;
    $data = [
      'nickname' => 'nickname',
      'email' => 'email@example.com',
      'password' => 'password',
      'active' => true,
      'created_in' => new Expression(Expression::CURRENT_TIMESTAMP),
      'updated_in' => new Timestamp('2022-01-01 00:00:00'),
      'inactivated_in' => null
    ];

    $insert->into($table::tableName())->values($data);

    $expectedSql = "INSERT INTO user (nickname, email, password, active, created_in, updated_in, inactivated_in) VALUES (:nickname, :email, :password, :active, :created_in, :updated_in, :inactivated_in)";
    $expectedParams = [
      ':nickname' => "'nickname'",
      ':email' => "'email@example.com'",
      ':password' => "'password'",
      ':active' => 'TRUE',
      ':created_in' => 'CURRENT_TIMESTAMP',
      ':updated_in' => "'2022-01-01 00:00:00'",
      ':inactivated_in' => 'NULL'
    ];

    $this->assertEquals($expectedSql, $insert->getSql());
    $this->assertEquals($expectedParams, $insert->getParameters());
  }

  public function testInsertWithTimestamp()
  {
    $insert = new Insert(Maestro::SQL_NO_PREDICT);
    $table = User::class;
    $date = new Timestamp();
    $data = ['nickname' => 'nickname', 'email' => 'email@example.com', 'created_at' => $date];

    $insert->into($table::tableName())->values($data);

    $expectedSql = "INSERT INTO user (nickname, email, created_at) VALUES (:nickname, :email, :created_at)";
    $expectedParams = [':nickname' => "'nickname'", ':email' => "'email@example.com'", ':created_at' => "'{$date}'"];

    $this->assertEquals($expectedSql, $insert->getSql());
    $this->assertEquals($expectedParams, $insert->getParameters());
  }

  public function testInsertWithNullValue()
  {
    $insert = new Insert(Maestro::SQL_NO_PREDICT);
    $table = User::class;
    $data = ['nickname' => 'nickname', 'email' => null, 'password' => 'password'];

    $insert->into($table::tableName())->values($data);

    $expectedSql = "INSERT INTO user (nickname, email, password) VALUES (:nickname, :email, :password)";
    $expectedParams = [':nickname' => "'nickname'", ':email' => 'NULL', ':password' => "'password'"];

    $this->assertEquals($expectedSql, $insert->getSql());
    $this->assertEquals($expectedParams, $insert->getParameters());
  }
}
