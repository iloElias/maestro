<?php

namespace Ilias\Maestro\Database;

use Ilias\Dotenv\Helper;

class PDOConnection
{
  private static string $sqlDatabase;

  private static string $host;

  private static string $port;

  private static string $databaseName;

  private static string $username;

  private static string $password;

  private static string $dns = "";

  private static ?\PDO $pdo = null;

  private function __construct()
  {
  }

  private function __clone()
  {
  }

  public function __wakeup()
  {
  }

  public static function getInstance(): \PDO
  {
    if (self::$pdo === null) {
      // self::$sqlDatabase = Helper::env("DB_SQL") ?? "pgsql";
      self::$host = Helper::env("DB_HOST") ?? "localhost";
      self::$port = Helper::env("DB_PORT") ?? "5432";
      self::$databaseName = Helper::env("DB_NAME") ?? "postgres";
      self::$username = Helper::env("DB_USER") ?? "postgres";
      self::$password = Helper::env("DB_PASS") ?? "";

      self::$dns = self::$sqlDatabase . ":host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$databaseName . ";user=" . self::$username . ";password=" . self::$password;
      self::$pdo = new \PDO(self::$dns);
      self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    return self::$pdo;
  }
}
