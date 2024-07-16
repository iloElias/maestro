<?php

namespace Ilias\Maestro\Database;

use Ilias\Dotenv\Helper;
use Ilias\Maestro\Core\Environment;

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

  public static function getPdoInstance(): \PDO
  {
    if (self::$pdo === null) {
      self::$sqlDatabase = Helper::env("DB_SQL");
      self::$host = Helper::env("DB_HOST");
      self::$port = Helper::env("DB_PORT");
      self::$databaseName = Helper::env("DB_NAME");
      self::$username = Helper::env("DB_USER");
      self::$password = Helper::env("DB_PASS");

      self::$dns = self::$sqlDatabase . ":host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$databaseName . ";user=" . self::$username . ";password=" . self::$password;
      self::$pdo = new \PDO(self::$dns);
      self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    return self::$pdo;
  }
}
