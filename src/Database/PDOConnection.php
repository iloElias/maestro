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

  public function __wakeup()
  {
  }

  /**
   * Retrieves a PDO connection instance.
   * This method returns a singleton instance of the PDO connection. If the connection has not been established yet, it will create a new one using the provided parameters or environment variables. If a PDO mock object is provided, it will be used instead.
   * @param string|null $dbSql The SQL driver (e.g., mysql, pgsql). Defaults to environment variable DB_SQL.
   * @param string|null $dbName The name of the database. Defaults to environment variable DB_NAME.
   * @param string|null $dbHost The database host. Defaults to environment variable DB_HOST.
   * @param string|null $dbPort The database port. Defaults to environment variable DB_PORT.
   * @param string|null $dbUser The database user. Defaults to environment variable DB_USER.
   * @param string|null $dbPass The database password. Defaults to environment variable DB_PASS.
   * @param \PDO|null $pdoMock An optional PDO mock object for testing purposes.
   * @return \PDO The PDO connection instance.
   */
  public static function get(string $dbSql = null, string $dbName = null, string $dbHost = null, string $dbPort = null, string $dbUser = null, string $dbPass = null, ?\PDO $pdoMock = null): \PDO
  {
    if (self::$pdo === null) {
      if ($pdoMock) {
        self::$pdo = $pdoMock;
      } else {
        $dbSql = $dbSql ?? Helper::env("DB_SQL");
        $dbName = $dbName ?? Helper::env("DB_NAME");
        $dbHost = $dbHost ?? Helper::env("DB_HOST");
        $dbPort = $dbPort ?? Helper::env("DB_PORT");
        $dbUser = $dbUser ?? Helper::env("DB_USER");
        $dbPass = $dbPass ?? Helper::env("DB_PASS");

        $dns = "{$dbSql}:host={$dbHost};port={$dbPort};dbname={$dbName}";
        self::$pdo = new \PDO($dns, $dbUser, $dbPass);
        self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
      }
    }
    return self::$pdo;
  }

  /**
   * Get an instance of the PDO connection.
   * @param \PDO|null $pdoMock Optional PDO mock object for testing.
   * @return \PDO The PDO connection instance.
   * @deprecated This method is deprecated and will be removed in a future version. Use self::get() directly instead.
   */
  public static function getInstance(?\PDO $pdoMock = null): \PDO
  {
    return self::get(pdoMock: $pdoMock);
  }
}
