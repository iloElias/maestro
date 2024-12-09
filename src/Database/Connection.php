<?php

namespace Ilias\Maestro\Database;

use Ilias\Dotenv\Environment;
use Ilias\Dotenv\Helper;
use PDO;

class Connection
{
  private static PDO $pdo;

  private function __construct()
  {
  }

  /**
   * Retrieves a PDO connection instance.
   * This method returns a singleton instance of the PDO connection. If the connection has not been established yet, it will create a new one using the provided parameters or environment variables. If a PDO mock object is provided, it will be used instead.
   * @param string|null $dbSql The SQL driver (e.g., mysql, pgsql). It will be automatically set from the environment variable `DB_CONNECTION`.
   * @param string|null $dbHost The database host. It will be automatically set from the environment variable `DB_HOST`.
   * @param string|null $dbPort The database port. It will be automatically set from the environment variable `DB_PORT`.
   * @param string|null $dbName The name of the database. It will be automatically set from the environment variable `DB_DATABASE`.
   * @param string|null $dbUser The database user. It will be automatically set from the environment variable `DB_USERNAME`.
   * @param string|null $dbPass The database password. It will be automatically set from the environment variable `DB_PASSWORD`.
   * @param PDO|null $pdoMock An optional PDO mock object for testing purposes.
   * @return PDO The PDO connection instance.
   */
  public static function get(string $dbSql = null, string $dbName = null, string $dbHost = null, string $dbPort = null, string $dbUser = null, string $dbPass = null, ?PDO $pdoMock = null): PDO
  {
    $data = [
      'DB_CONNECTION' => $dbSql,
      'DB_HOST' => $dbHost,
      'DB_PORT' => $dbPort,
      'DB_DATABASE' => $dbName,
      'DB_USERNAME' => $dbUser,
      'DB_PASSWORD' => $dbPass,
    ];
    $data = self::getEnv($data);
    if (empty(self::$pdo)) {
      if ($pdoMock) {
        self::$pdo = $pdoMock;
      } else {
        $dsn = "{$dbSql}:host={$dbHost};port={$dbPort};dbname={$dbName}";
        self::$pdo = new PDO($dsn, $dbUser, $dbPass);
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
    }
    return self::$pdo;
  }

  /**
   * Get an instance of the PDO connection.
   * @param PDO|null $pdoMock Optional PDO mock object for testing.
   * @return PDO The PDO connection instance.
   * @deprecated This method is deprecated and will be removed in a future version. Use self::get() directly instead.
   */
  public static function getInstance(?PDO $pdoMock = null): PDO
  {
    if (!empty($pdoMock)) {
      return self::get(dbName: "mock", pdoMock: $pdoMock);
    }
    return self::get();
  }

  private static function getEnv(array $data): array
  {
    foreach ($data as $key => $value) {
      $data[$key] = Helper::env($key, $value);
    }
    return $data;
  }
}
