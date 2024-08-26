<?php

require_once 'vendor/autoload.php';

use Ilias\Maestro\Core\Manager;
use Ilias\Maestro\Core\Synchronizer;
use Ilias\Maestro\Database\Select;
use Maestro\Example\MaestroDb;
use Maestro\Example\User;

// var_dump(MaestroDb::dumpDatabase());
// MaestroDb::prettyPrint();


// $coreDatabase = new Manager();
// $maestroDb = new MaestroDb();

// print implode("\n", $coreDatabase->createDatabase($maestroDb, false)) . "\n";


// echo $coreDatabase->createTable(User::class) . "\n";
// var_dump(User::getTableCreationInfo());

// $ormDb = new MaestroDb();

// $synchronizer = new Synchronizer();
// $synchronizer->synchronize($ormDb);

$select = new Select();
$select->from('users')
  ->select('id', 'name')
  ->where(['id' => 1])
  ->in(['id' => [1, 2, 3]])
  ->order('name', 'DESC');

echo $select->getSql() . "\n";

