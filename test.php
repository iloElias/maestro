<?php

require_once 'vendor/autoload.php';

use Ilias\Maestro\Core\Database;
use Maestro\Example\Hr;
use Maestro\Example\MaestroDb;
use Maestro\Example\User;

// var_dump(MaestroDb::dumpDatabase());
// MaestroDb::prettyPrint();

$coreDatabase = new Database();
$maestroDb = new MaestroDb();

echo implode("\n", $coreDatabase->createDatabase($maestroDb)) . "\n";

// echo $coreDatabase->createTable(User::class) . "\n";
