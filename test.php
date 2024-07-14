<?php

require_once 'vendor/autoload.php';

use Ilias\Maestro\Core\Database;
use Ilias\Maestro\TestClasses\Hr;
use Ilias\Maestro\TestClasses\MaestroDb;

// var_dump(MaestroDb::dumpDatabase());
// MaestroDb::prettyPrint();

$coreDatabase = new Database();
$hrSchema = new Hr();

echo implode("\n", $coreDatabase->createTablesForSchema($hrSchema));
