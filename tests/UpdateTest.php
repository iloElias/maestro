<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ilias\Maestro\Database\Update;
use Maestro\Example\User;

class UpdateTest extends TestCase
{
  public function testUpdate()
  {
    $update = new Update();
    $table = User::class;
    $data = ['nickname' => 'updated_nickname'];
    $conditions = ['email' => 'email@example.com'];

    $update->table($table::getTableName())->set('nickname', 'updated_nickname')->where($conditions);

    $expectedSql = "UPDATE user SET nickname = :nickname WHERE email = :where_email";
    $expectedParams = [':nickname' => 'updated_nickname', ':where_email' => 'email@example.com'];

    $this->assertEquals($expectedSql, $update->getSql());
    $this->assertEquals($expectedParams, $update->getParameters());
  }

  public function testUpdateWithInClause()
  {
    $update = new Update();
    $table = User::class;
    $data = ['nickname' => 'updated_nickname'];
    $conditions = ['id' => [1, 2, 3]];

    $update->table($table::getTableName())->set('nickname', 'updated_nickname')->in($conditions);

    $expectedSql = "UPDATE user SET nickname = :nickname WHERE id IN(:in_id_0,:in_id_1,:in_id_2)";
    $expectedParams = [':nickname' => 'updated_nickname', ':in_id_0' => 1, ':in_id_1' => 2, ':in_id_2' => 3];

    $this->assertEquals($expectedSql, $update->getSql());
    $this->assertEquals($expectedParams, $update->getParameters());
  }

  public function testUpdateWithMultipleConditions()
  {
    $update = new Update();
    $table = User::class;
    $data = ['nickname' => 'updated_nickname'];
    $conditions = ['email' => 'email@example.com', 'active' => true];

    $update->table($table::getTableName())->set('nickname', 'updated_nickname')->where($conditions);

    $expectedSql = "UPDATE user SET nickname = :nickname WHERE email = :where_email AND active = :where_active";
    $expectedParams = [':nickname' => 'updated_nickname', ':where_email' => 'email@example.com', ':where_active' => true];

    $this->assertEquals($expectedSql, $update->getSql());
    $this->assertEquals($expectedParams, $update->getParameters());
  }

  public function testMultipleInConditions()
  {
    $update = new Update();
    $table = User::class;
    $data = ['nickname' => 'updated_nickname'];
    $conditions = [
      'id' => [1, 2, 3],
      'group_id' => [10, 20, 30]
    ];

    $update->table($table::getTableName())->set('nickname', 'updated_nickname')->in($conditions);

    $expectedSql = "UPDATE user SET nickname = :nickname WHERE id IN(:in_id_0,:in_id_1,:in_id_2) AND group_id IN(:in_group_id_0,:in_group_id_1,:in_group_id_2)";
    $expectedParams = [
      ':nickname' => 'updated_nickname',
      ':in_id_0' => 1, ':in_id_1' => 2, ':in_id_2' => 3,
      ':in_group_id_0' => 10, ':in_group_id_1' => 20, ':in_group_id_2' => 30
    ];

    $this->assertEquals($expectedSql, $update->getSql());
    $this->assertEquals($expectedParams, $update->getParameters());
  }

  public function testUpdateWithoutConditions()
  {
    $update = new Update();
    $table = User::class;
    $data = ['nickname' => 'updated_nickname'];

    $update->table($table::getTableName())->set('nickname', 'updated_nickname');

    $expectedSql = "UPDATE user SET nickname = :nickname";
    $expectedParams = [':nickname' => 'updated_nickname'];

    $this->assertEquals($expectedSql, $update->getSql());
    $this->assertEquals($expectedParams, $update->getParameters());
  }

  public function testUpdateWithDateTime()
  {
    $update = new Update();
    $table = User::class;
    $date = new \DateTime();
    $data = ['updated_at' => $date];
    $conditions = ['email' => 'email@example.com'];

    $update->table($table::getTableName())->set('updated_at', $date)->where($conditions);

    $expectedSql = "UPDATE user SET updated_at = :updated_at WHERE email = :where_email";
    $expectedParams = [':updated_at' => $date, ':where_email' => 'email@example.com'];

    $this->assertEquals($expectedSql, $update->getSql());
    $this->assertEquals($expectedParams, $update->getParameters());
  }

  public function testUpdateMultipleSetClauses()
  {
    $update = new Update();
    $table = User::class;
    $data = ['nickname' => 'updated_nickname', 'active' => false];
    $conditions = ['email' => 'email@example.com'];

    $update->table($table::getTableName())->set('nickname', 'updated_nickname')->set('active', false)->where($conditions);

    $expectedSql = "UPDATE user SET nickname = :nickname, active = :active WHERE email = :where_email";
    $expectedParams = [':nickname' => 'updated_nickname', ':active' => false, ':where_email' => 'email@example.com'];

    $this->assertEquals($expectedSql, $update->getSql());
    $this->assertEquals($expectedParams, $update->getParameters());
  }
}