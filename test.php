<?php

require_once 'vendor/autoload.php';

use Ilias\Maestro\Core\Manager;
use Ilias\Maestro\Core\Synchronizer;
use Maestro\Example\MaestroDb;
use Maestro\Example\User;

// var_dump(MaestroDb::dumpDatabase());
// MaestroDb::prettyPrint();

$coreDatabase = new Manager();
$maestroDb = new MaestroDb();

print implode("\n", $coreDatabase->createDatabase($maestroDb, true)) . "\n";

// echo $coreDatabase->createTable(User::class) . "\n";
// var_dump(User::getTableCreationInfo());

// $ormDb = new MaestroDb();

// $synchronizer = new Synchronizer();
// $synchronizer->synchronize($ormDb);