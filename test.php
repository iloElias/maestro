<?php

require_once 'vendor/autoload.php';

use Ilias\Maestro\Core\Manager;
use Ilias\Maestro\Database\Delete;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\PDOConnection;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Database\SqlBehavior;
use Ilias\Maestro\Types\Timestamp;
use Maestro\Example\MaestroDb;
use Maestro\Example\User;

// var_dump(MaestroDb::dumpDatabase());
// MaestroDb::prettyPrint();

// $coreDatabase = new Manager();
// $maestroDb = new MaestroDb();

// print implode("\n", $coreDatabase->createDatabase($maestroDb)) . "\n";
// foreach ($coreDatabase->createDatabase($maestroDb) as $process) {
//   $coreDatabase->executeQuery(PDOConnection::getInstance(), $process);
// }

$userTest1 = new User('John Doe', 'johndoe@email.com', md5('abc123'), true, new Timestamp('now'));
$userTest2 = new User('Jane Doe', 'janedoe@email.com', md5('abc123'), true, new Timestamp('now'));

$insert1 = new Insert(SqlBehavior::SQL_STRICT, PDOConnection::getInstance());
$insert1->into(User::class)
  ->values($userTest1)
  ->returning(['id']);

$insert2 = new Insert(SqlBehavior::SQL_STRICT, PDOConnection::getInstance());
$insert2->into(User::class)
  ->values($userTest2)
  ->returning(['id']);

$id1 = $insert1->bindParameters()->execute();
$id2 = $insert2->bindParameters()->execute();

$delete = new Delete(SqlBehavior::SQL_STRICT, PDOConnection::getInstance());
$delete->from(User::class)
  ->in(['id' => [$id1[0]['id'], $id2[0]['id']]]);
$delete->bindParameters()->execute();

// echo $coreDatabase->createTable(User::class) . "\n";
