<?php

require_once __DIR__ . "/vendor/autoload.php";

use Ilias\Dotenv\Helper;
use Ilias\Maestro\Database\PDOConnection;
use Ilias\Maestro\Core\Maestro;

$dbSql = Helper::env("DB_SQL", "pgsql");
$dbName = Helper::env("DB_NAME");
$dbHost = Helper::env("DB_HOST", "localhost");
$dbPort = Helper::env("DB_PORT", "5432");
$dbUser = Helper::env("DB_USER", "postgres");
$dbPass = Helper::env("DB_PASS");

PDOConnection::get(
  $dbSql,
  $dbName,
  $dbHost,
  $dbPort,
  $dbUser,
  $dbPass,
);

$maestroConfig = new Maestro();
