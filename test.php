<?php

require_once __DIR__ . '/vendor/autoload.php';

use Ilias\Maestro\Core\Manager;
use Maestro\Example\MaestroDb;

$maestroDB = new MaestroDb();
$manager = new Manager();

echo implode("\n", $manager->createDatabase($maestroDB, false)) . "\n";

