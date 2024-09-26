<?php

namespace Tests\Unit;

use Ilias\Maestro\Database\Expression;
use PHPUnit\Framework\TestCase;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Core\Maestro;
use Maestro\Example\User;

class SelectTest extends TestCase
{
  public function testSelect()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['user' => 'user'], ['nickname', 'email'])->where(['active' => true]);

    $expectedSql = "SELECT user.nickname, user.email FROM user WHERE active = :where_active";
    $expectedParams = [':where_active' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithInClause()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['user' => 'user'], ['nickname', 'email'])->in(['id' => [1, 2, 3]]);

    $expectedSql = "SELECT user.nickname, user.email FROM user WHERE id IN(:in_id_0,:in_id_1,:in_id_2)";
    $expectedParams = [':in_id_0' => 1, ':in_id_1' => 2, ':in_id_2' => 3];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithMultipleConditions()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['user' => 'user'], ['nickname', 'email'])->where(['active' => true, 'verified' => true]);

    $expectedSql = "SELECT user.nickname, user.email FROM user WHERE active = :where_active AND verified = :where_verified";
    $expectedParams = [':where_active' => true, ':where_verified' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testMultipleInConditions()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $table = User::class;
    $columns = ['nickname', 'email'];
    $conditions = [
      'id' => [1, 2, 3],
      'group_id' => [10, 20, 30]
    ];

    $select->from([$table::tableName() => 'user'], $columns)->in($conditions);

    $expectedSql = "SELECT user.nickname, user.email FROM user WHERE id IN(:in_id_0,:in_id_1,:in_id_2) AND group_id IN(:in_group_id_0,:in_group_id_1,:in_group_id_2)";
    $expectedParams = [
      ':in_id_0' => 1,
      ':in_id_1' => 2,
      ':in_id_2' => 3,
      ':in_group_id_0' => 10,
      ':in_group_id_1' => 20,
      ':in_group_id_2' => 30
    ];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithoutConditions()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['user' => 'user'], ['nickname', 'email']);

    $expectedSql = "SELECT user.nickname, user.email FROM user";
    $expectedParams = [];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithFunctions()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $table = User::class;
    $columns = ['nickname', 'email', 'count' => new Expression('COUNT(*)')];
    $conditions = ['active' => true];

    $select->from([$table::tableName() => 'user'], $columns)->where($conditions);

    $expectedSql = "SELECT user.nickname, user.email, COUNT(*) AS count FROM user WHERE active = :where_active";
    $expectedParams = [':where_active' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithJoin()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $table = User::class;
    $columns = ['nickname', 'email'];
    $conditions = ['user.active' => true];

    $select->from(['user' => $table::tableName()], $columns)
      ->join(['profile' => 'profile'], 'user.id = profile.user_id', ['bio'])
      ->where($conditions);

    $expectedSql = "SELECT user.nickname, user.email, profile.bio FROM user AS user INNER JOIN profile AS profile ON user.id = profile.user_id WHERE user.active = :where_user_active";
    $expectedParams = [':where_user_active' => 'true'];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithLeftJoin()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $table = User::class;
    $columns = ['nickname', 'email'];
    $conditions = ['user.active' => true];

    $select->from(['user' => $table::tableName()], $columns)
      ->join(['profile' => 'profile'], 'user.id = profile.user_id', ['bio'], 'LEFT')
      ->where($conditions);

    $expectedSql = "SELECT user.nickname, user.email, profile.bio FROM user AS user LEFT JOIN profile AS profile ON user.id = profile.user_id WHERE user.active = :where_user_active";
    $expectedParams = [':where_user_active' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithMultipleJoins()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $table = User::class;
    $columns = ['nickname', 'email'];
    $conditions = ['user.active' => true];

    $select->from(['user' => $table::tableName()], $columns)
      ->join(['profile' => 'profile'], 'user.id = profile.user_id', ['bio'])
      ->join(['account' => 'account'], 'user.id = account.user_id', ['balance'])
      ->where($conditions);

    $expectedSql = "SELECT user.nickname, user.email, profile.bio, account.balance FROM user AS user INNER JOIN profile AS profile ON user.id = profile.user_id INNER JOIN account AS account ON user.id = account.user_id WHERE user.active = :where_user_active";
    $expectedParams = [':where_user_active' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithOrderBy()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $table = User::class;
    $columns = ['nickname', 'email'];
    $conditions = ['active' => true];

    $select->from([$table::tableName() => 'user'], $columns)->where($conditions)->order('nickname', 'ASC');

    $expectedSql = "SELECT user.nickname, user.email FROM user WHERE active = :where_active ORDER BY nickname ASC";
    $expectedParams = [':where_active' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }
}
