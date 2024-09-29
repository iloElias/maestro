<?php

use Ilias\Maestro\Core\Manager;
use Maestro\Example\MaestroDb;

require_once("./vendor/autoload.php");

$coreDatabase = new Manager();
$agrofastDB = new MaestroDb();

print implode("\n", $coreDatabase->createDatabase($agrofastDB, false)) . "\n";
