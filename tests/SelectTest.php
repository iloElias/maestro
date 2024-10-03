<?php

namespace Tests\Unit;

use Ilias\Maestro\Database\Expression;
use PHPUnit\Framework\TestCase;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Core\Maestro;
use InvalidArgumentException;
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

  public function testSelectWithGroupByAndHaving()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['user' => 'user'], ['nickname', 'count' => new Expression('COUNT(*)')])
      ->group(['nickname'])
      ->having(['count' => 1]);

    $expectedSql = "SELECT user.nickname, COUNT(*) AS count FROM user GROUP BY nickname HAVING count = :having_count";
    $expectedParams = [':having_count' => 1];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithOffsetAndLimit()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['user' => 'user'], ['nickname', 'email'])
      ->limit(10)
      ->offset(5);

    $expectedSql = "SELECT user.nickname, user.email FROM user LIMIT 10 OFFSET 5";
    $expectedParams = [];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testComplexSelectQuery()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['user' => 'user'], ['nickname', 'email'])
      ->join(['profile' => 'profile'], 'user.id = profile.user_id', ['bio'])
      ->where(['user.active' => true])
      ->group(['user.nickname'])
      ->having([(string) new Expression('COUNT(profile.id)') => 1])
      ->order('user.nickname', 'ASC')
      ->limit(10)
      ->offset(5);

    $expectedSql = "SELECT user.nickname, user.email, profile.bio FROM user AS user INNER JOIN profile AS profile ON user.id = profile.user_id WHERE user.active = :where_user_active GROUP BY user.nickname HAVING COUNT(profile.id) = :having_count_profile_id ORDER BY user.nickname ASC LIMIT 10 OFFSET 5";
    $expectedParams = [
      ':where_user_active' => true,
      ':having_count_profile_id' => 1
    ];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithDistinct()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->distinct()->from(['user' => 'user'], ['nickname', 'email']);

    $expectedSql = "SELECT DISTINCT user.nickname, user.email FROM user";
    $expectedParams = [];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithRightJoin()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $table = User::class;
    $columns = ['nickname', 'email'];
    $conditions = ['user.active' => true];

    $select->from(['user' => $table::tableName()], $columns)
      ->join(['profile' => 'profile'], 'user.id = profile.user_id', ['bio'], 'RIGHT')
      ->where($conditions);

    $expectedSql = "SELECT user.nickname, user.email, profile.bio FROM user AS user RIGHT JOIN profile AS profile ON user.id = profile.user_id WHERE user.active = :where_user_active";
    $expectedParams = [':where_user_active' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithGroupBy()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['user' => 'user'], ['nickname', 'email'])
      ->group(['nickname']);

    $expectedSql = "SELECT user.nickname, user.email FROM user GROUP BY nickname";
    $expectedParams = [];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithMultipleHavingConditions()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['user' => 'user'], ['nickname', 'count' => new Expression('COUNT(*)')])
      ->group(['nickname'])
      ->having(['count' => 1, 'nickname' => 'test']);

    $expectedSql = "SELECT user.nickname, COUNT(*) AS count FROM user GROUP BY nickname HAVING count = :having_count AND nickname = :having_nickname";
    $expectedParams = [':having_count' => 1, ':having_nickname' => "'test'"];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithOrderByDesc()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $table = User::class;
    $columns = ['nickname', 'email'];
    $conditions = ['active' => true];

    $select->from([$table::tableName() => 'user'], $columns)->where($conditions)->order('nickname', 'DESC');

    $expectedSql = "SELECT user.nickname, user.email FROM user WHERE active = :where_active ORDER BY nickname DESC";
    $expectedParams = [':where_active' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithWhereDifferentDataTypes()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['user' => 'user'], ['nickname', 'email'])
      ->where(['active' => true, 'age' => 30, 'name' => 'John']);

    $expectedSql = "SELECT user.nickname, user.email FROM user WHERE active = :where_active AND age = :where_age AND name = :where_name";
    $expectedParams = [':where_active' => 'true', ':where_age' => 30, ':where_name' => "'John'"];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithInClauseEmptyArray()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['user' => 'user'], ['nickname', 'email'])->in(['id' => []]);

    $expectedSql = "SELECT user.nickname, user.email FROM user WHERE id IN()";
    $expectedParams = [];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithOrderByInvalidDirection()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $table = User::class;
    $columns = ['nickname', 'email'];
    $conditions = ['active' => true];

    $select->from([$table::tableName() => 'user'], $columns)->where($conditions)->order('nickname', 'INVALID');

    $expectedSql = "SELECT user.nickname, user.email FROM user WHERE active = :where_active ORDER BY nickname INVALID";
    $expectedParams = [':where_active' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithInvalidLimit()
  {
    $this->expectException(InvalidArgumentException::class);
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['user' => 'user'], ['nickname', 'email'])
      ->limit('invalid');
  }

  public function testSelectWithComplexQuery()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['user' => 'user'], ['nickname', 'email'])
      ->join(['profile' => 'profile'], 'user.id = profile.user_id', ['bio'])
      ->where(['user.active' => true])
      ->group(['user.nickname'])
      ->having([(string)new Expression('COUNT(profile.id)') => 1])
      ->order('user.nickname', 'DESC')
      ->limit(10)
      ->offset(5);

    $expectedSql = "SELECT user.nickname, user.email, profile.bio FROM user AS user INNER JOIN profile AS profile ON user.id = profile.user_id WHERE user.active = :where_user_active GROUP BY user.nickname HAVING COUNT(profile.id) = :having_count_profile_id ORDER BY user.nickname DESC LIMIT 10 OFFSET 5";
    $expectedParams = [':where_user_active' => true, ':having_count_profile_id' => 1];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithSqlInjectionPrevention()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['user' => 'user'], ['nickname', 'email'])
      ->where(['user.nickname' => "'; DROP TABLE users; --"]);

    $expectedSql = "SELECT user.nickname, user.email FROM user WHERE user.nickname = :where_user_nickname";
    $expectedParams = [':where_user_nickname' => "'; DROP TABLE users; --"];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithPagination()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['user' => 'user'], ['nickname', 'email'])
      ->limit(10)
      ->offset(20);

    $expectedSql = "SELECT user.nickname, user.email FROM user LIMIT 10 OFFSET 20";
    $expectedParams = [];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithAggregateFunctions()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['user' => 'user'], ['nickname', 'total' => new Expression('SUM(amount)')])
      ->group(['user.nickname']);

    $expectedSql = "SELECT user.nickname, SUM(amount) AS total FROM user GROUP BY user.nickname";
    $expectedParams = [];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }

  public function testSelectWithAliases()
  {
    $select = new Select(Maestro::SQL_NO_PREDICT);
    $select->from(['u' => 'user'], ['u.nickname', 'u.email'])
      ->join(['p' => 'profile'], 'u.id = p.user_id', ['p.bio'])
      ->where(['u.active' => true]);

    $expectedSql = "SELECT u.nickname, u.email, p.bio FROM user AS u JOIN profile AS p ON u.id = p.user_id WHERE u.active = :where_u_active";
    $expectedParams = [':where_u_active' => true];

    $this->assertEquals($expectedSql, $select->getSql());
    $this->assertEquals($expectedParams, $select->getParameters());
  }
}
