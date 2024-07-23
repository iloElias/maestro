<?php

require_once 'vendor/autoload.php';

use Ilias\Maestro\Core\Manager;
use Maestro\Example\MarkfyDb;

// var_dump(MaestroDb::dumpDatabase());
// MaestroDb::prettyPrint();

$coreDatabase = new Manager();
$maestroDb = new MarkfyDb();

print implode("\n", $coreDatabase->createDatabase($maestroDb)) . "\n";

// echo $coreDatabase->createTable(User::class) . "\n";
