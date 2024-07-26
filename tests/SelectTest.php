<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ilias\Maestro\Database\Select;
use Maestro\Example\User;

class SelectTest extends TestCase
{
  public function testSelect()
  {
    $select = new Select();
    $select->select('nickname', 'email')->from('user')->where(['active' => true]);

    $expectedSql = "SELECT nickname, email FROM user WHERE active = :where_active";
    $expectedParams = [':where_active' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithInClause()
  {
    $select = new Select();
    $select->select('nickname', 'email')->from('user')->in(['id' => [1, 2, 3]]);

    $expectedSql = "SELECT nickname, email FROM user WHERE id IN(:in_id_0,:in_id_1,:in_id_2)";
    $expectedParams = [':in_id_0' => 1, ':in_id_1' => 2, ':in_id_2' => 3];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithMultipleConditions()
  {
    $select = new Select();
    $select->select('nickname', 'email')->from('user')->where(['active' => true, 'verified' => true]);

    $expectedSql = "SELECT nickname, email FROM user WHERE active = :where_active AND verified = :where_verified";
    $expectedParams = [':where_active' => true, ':where_verified' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testMultipleInConditions()
  {
    $select = new Select();
    $table = User::class;
    $columns = ['nickname', 'email'];
    $conditions = [
      'id' => [1, 2, 3],
      'group_id' => [10, 20, 30]
    ];

    $select->select(...$columns)->from($table::getTableName())->in($conditions);

    $expectedSql = "SELECT nickname, email FROM user WHERE id IN(:in_id_0,:in_id_1,:in_id_2) AND group_id IN(:in_group_id_0,:in_group_id_1,:in_group_id_2)";
    $expectedParams = [
      ':in_id_0' => 1, ':in_id_1' => 2, ':in_id_2' => 3,
      ':in_group_id_0' => 10, ':in_group_id_1' => 20, ':in_group_id_2' => 30
    ];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithoutConditions()
  {
    $select = new Select();
    $select->select('nickname', 'email')->from('user');

    $expectedSql = "SELECT nickname, email FROM user";
    $expectedParams = [];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithFunctions()
  {
    $select = new Select();
    $table = User::class;
    $columns = ['nickname', 'email', 'COUNT(*) as count'];
    $conditions = ['active' => true];

    $select->select(...$columns)->from($table::getTableName())->where($conditions);

    $expectedSql = "SELECT nickname, email, COUNT(*) as count FROM user WHERE active = :where_active";
    $expectedParams = [':where_active' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithJoin()
  {
    $select = new Select();
    $table = User::class;
    $columns = ['user.nickname', 'user.email', 'profile.bio'];
    $conditions = ['user.active' => true];

    $select->select(...$columns)
      ->from($table::getTableName())
      ->join('profile', 'user.id = profile.user_id')
      ->where($conditions);

    $expectedSql = "SELECT user.nickname, user.email, profile.bio FROM user INNER JOIN profile ON user.id = profile.user_id WHERE user.active = :where_user_active";
    $expectedParams = [':where_user_active' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithLeftJoin()
  {
    $select = new Select();
    $table = User::class;
    $columns = ['user.nickname', 'user.email', 'profile.bio'];
    $conditions = ['user.active' => true];

    $select->select(...$columns)
      ->from($table::getTableName())
      ->join('profile', 'user.id = profile.user_id', 'LEFT')
      ->where($conditions);

    $expectedSql = "SELECT user.nickname, user.email, profile.bio FROM user LEFT JOIN profile ON user.id = profile.user_id WHERE user.active = :where_user_active";
    $expectedParams = [':where_user_active' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithMultipleJoins()
  {
    $select = new Select();
    $table = User::class;
    $columns = ['user.nickname', 'user.email', 'profile.bio', 'account.balance'];
    $conditions = ['user.active' => true];

    $select->select(...$columns)
      ->from($table::getTableName())
      ->join('profile', 'user.id = profile.user_id')
      ->join('account', 'user.id = account.user_id')
      ->where($conditions);

    $expectedSql = "SELECT user.nickname, user.email, profile.bio, account.balance FROM user INNER JOIN profile ON user.id = profile.user_id INNER JOIN account ON user.id = account.user_id WHERE user.active = :where_user_active";
    $expectedParams = [':where_user_active' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithOrderBy()
  {
    $select = new Select();
    $table = User::class;
    $columns = ['nickname', 'email'];
    $conditions = ['active' => true];

    $select->select(...$columns)->from($table::getTableName())->where($conditions)->order('nickname', 'ASC');

    $expectedSql = "SELECT nickname, email FROM user WHERE active = :where_active ORDER BY nickname ASC";
    $expectedParams = [':where_active' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }
}
