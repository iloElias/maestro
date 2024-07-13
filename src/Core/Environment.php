<?php

namespace Ilias\Maestro\Core;

class Environment
{
  private const ENV_FILE_PATH = '.env';
  private static array $vars = [];

  public static function getEnvironment(): void
  {
    $documentRoot = empty($_SERVER["DOCUMENT_ROOT"]) ? $_SERVER["OLDPWD"] : $_SERVER["DOCUMENT_ROOT"];
    $envFile = $documentRoot . DIRECTORY_SEPARATOR . self::ENV_FILE_PATH;

    try {
      $envContent = self::loadEnvFile($envFile);
    } catch (\Throwable $th) {
      // TODO: handler
      return;
    }

    $envLines = explode("\n", $envContent);
    self::processEnvLines($envLines);
  }

  private static function loadEnvFile(string $envFile)
  {
    if (file_exists($envFile)) {
      return file_get_contents($envFile) ?? null;
    }
  }

  private static function processEnvLines(array $envLines): void
  {
    foreach ($envLines as $envLine) {
      if (self::isValidEnvLine($envLine)) {
        [$name, $value] = self::parseEnvLine($envLine);
        $value = self::normalizeEnvValue($value);

        self::setEnvironmentVariable($name, $value);
      }
    }
  }

  private static function isValidEnvLine(string $envLine): bool
  {
    return $envLine !== '' && $envLine !== '0' && strpos($envLine, '#') !== 0;
  }

  private static function parseEnvLine(string $envLine): array
  {
    [$name, $value] = explode('=', $envLine, 2);
    return [trim($name), trim(str_replace('"', '', $value))];
  }

  private static function normalizeEnvValue(string $value)
  {
    switch (strtolower($value)) {
      case 'true':
      case '(true)':
        return true;
      case 'false':
      case '(false)':
        return false;
      case 'empty':
      case '(empty)':
        return '';
      case 'null':
      case '(null)':
        return null;
      default:
        return $value;
    }
  }

  private static function setEnvironmentVariable(string $name, $value): void
  {
    putenv(sprintf('%s=%s', $name, $value));
    self::$vars[$name] = $value;
  }

  public static function var(string $key)
  {
    if (isset(self::$vars[$key])) {
      return self::$vars[$key];
    }
  }
}