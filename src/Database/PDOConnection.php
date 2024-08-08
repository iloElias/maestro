<?php

namespace Ilias\Maestro\Database;

use Ilias\Dotenv\Helper;

class PDOConnection
{
  private static ?\PDO $pdo = null;

  private function __construct()
  {
  }

  private function __clone()
  {
  }

  private function __wakeup()
  {
  }

  public static function getInstance(?\PDO $pdoMock = null): \PDO
  {
    if (self::$pdo === null) {
      if ($pdoMock) {
        self::$pdo = $pdoMock;
      } else {
        $sqlDatabase = Helper::env("DB_SQL");
        $host = Helper::env("DB_HOST");
        $port = Helper::env("DB_PORT");
        $databaseName = Helper::env("DB_NAME");
        $username = Helper::env("DB_USER");
        $password = Helper::env("DB_PASS");

        $dns = "{$sqlDatabase}:host={$host};port={$port};dbname={$databaseName}";
        self::$pdo = new \PDO($dns, $username, $password);
        self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
      }
    }

    return self::$pdo;
  }
}
