<?php

namespace Maestro\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ilias\Maestro\Database\Delete;
use Ilias\Maestro\Core\Maestro;
use Ilias\Maestro\Types\Timestamp;
use Maestro\Example\User;

class DeleteTest extends TestCase
{
    public function testDelete()
    {
        $delete     = new Delete(Maestro::SQL_NO_PREDICT);
        $table      = User::class;
        $conditions = ['email' => 'email@example.com'];

        $delete->from($table::tableName())->where($conditions);

        $expectedSql    = 'DELETE FROM user WHERE email = :where_email';
        $expectedParams = [':where_email' => "'email@example.com'"];

        $this->assertEquals($expectedSql, $delete->getSql());
        $this->assertEquals($expectedParams, $delete->getParameters());
    }

    public function testDeleteWithInClause()
    {
        $delete     = new Delete(Maestro::SQL_NO_PREDICT);
        $table      = User::class;
        $conditions = ['id' => [1, 2, 3]];

        $delete->from($table::tableName())->in($conditions);

        $expectedSql    = 'DELETE FROM user WHERE id IN(:in_id_0,:in_id_1,:in_id_2)';
        $expectedParams = [':in_id_0' => 1, ':in_id_1' => 2, ':in_id_2' => 3];

        $this->assertEquals($expectedSql, $delete->getSql());
        $this->assertEquals($expectedParams, $delete->getParameters());
    }

    public function testDeleteWithMultipleConditions()
    {
        $delete     = new Delete(Maestro::SQL_NO_PREDICT);
        $table      = User::class;
        $conditions = ['email' => 'email@example.com', 'active' => false];

        $delete->from($table::tableName())->where($conditions);

        $expectedSql    = 'DELETE FROM user WHERE email = :where_email AND active = :where_active';
        $expectedParams = [':where_email' => "'email@example.com'", ':where_active' => 'FALSE'];

        $this->assertEquals($expectedSql, $delete->getSql());
        $this->assertEquals($expectedParams, $delete->getParameters());
    }

    public function testDeleteWithoutConditions()
    {
        $delete = new Delete(Maestro::SQL_NO_PREDICT);
        $table  = User::class;

        $delete->from($table::tableName());

        $expectedSql    = 'DELETE FROM user';
        $expectedParams = [];

        $this->assertEquals($expectedSql, $delete->getSql());
        $this->assertEquals($expectedParams, $delete->getParameters());
    }

    public function testMultipleInConditions()
    {
        $delete     = new Delete(Maestro::SQL_NO_PREDICT);
        $table      = User::class;
        $conditions = [
          'id'       => [1, 2, 3],
          'group_id' => [10, 20, 30],
        ];

        $delete->from($table::tableName())->in($conditions);

        $expectedSql    = 'DELETE FROM user WHERE id IN(:in_id_0,:in_id_1,:in_id_2) AND group_id IN(:in_group_id_0,:in_group_id_1,:in_group_id_2)';
        $expectedParams = [
          ':in_id_0'       => 1,
          ':in_id_1'       => 2,
          ':in_id_2'       => 3,
          ':in_group_id_0' => 10,
          ':in_group_id_1' => 20,
          ':in_group_id_2' => 30,
        ];

        $this->assertEquals($expectedSql, $delete->getSql());
        $this->assertEquals($expectedParams, $delete->getParameters());
    }

    public function testDeleteWithTimestampCondition()
    {
        $delete     = new Delete(Maestro::SQL_NO_PREDICT);
        $table      = User::class;
        $date       = new Timestamp();
        $conditions = ['created_at' => $date];

        $delete->from($table::tableName())->where($conditions);

        $expectedSql    = 'DELETE FROM user WHERE created_at = :where_created_at';
        $expectedParams = [':where_created_at' => "'{$date}'"];

        $this->assertEquals($expectedSql, $delete->getSql());
        $this->assertEquals($expectedParams, $delete->getParameters());
    }
}
