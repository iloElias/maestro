<?php

namespace Ilias\Maestro\Database;

use PDO;

class PDOConnection
{
  private static PDO $pdo;

  private function __construct()
  {
  }

  /**
   * Retrieves a PDO connection instance.
   * This method returns a singleton instance of the PDO connection. If the connection has not been established yet, it will create a new one using the provided parameters or environment variables. If a PDO mock object is provided, it will be used instead.
   * @param string|null $dbSql The SQL driver (e.g., mysql, pgsql).
   * @param string|null $dbName The name of the database.
   * @param string|null $dbHost The database host.
   * @param string|null $dbPort The database port.
   * @param string|null $dbUser The database user.
   * @param string|null $dbPass The database password.
   * @param PDO|null $pdoMock An optional PDO mock object for testing purposes.
   * @return PDO The PDO connection instance.
   */
  public static function get(string $dbSql = null, string $dbName = null, string $dbHost = null, string $dbPort = null, string $dbUser = null, string $dbPass = null, ?PDO $pdoMock = null): PDO
  {
    if (empty(self::$pdo)) {
      if ($pdoMock) {
        self::$pdo = $pdoMock;
      } else {
        $dns = "{$dbSql}:host={$dbHost};port={$dbPort};dbname={$dbName}";
        self::$pdo = new PDO($dns, $dbUser, $dbPass);
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
}
