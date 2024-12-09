<?php

require_once __DIR__ . '/vendor/autoload.php';

use Ilias\Dotenv\Helper;
use Ilias\Maestro\Database\Connection;

$dbSql  = Helper::env('DB_SQL', 'pgsql');
$dbName = Helper::env('DB_NAME');
$dbHost = Helper::env('DB_HOST', 'localhost');
$dbPort = Helper::env('DB_PORT', '5432');
$dbUser = Helper::env('DB_USER', 'postgres');
$dbPass = Helper::env('DB_PASS');

Connection::get(
    $dbSql,
    $dbName,
    $dbHost,
    $dbPort,
    $dbUser,
    $dbPass,
);
