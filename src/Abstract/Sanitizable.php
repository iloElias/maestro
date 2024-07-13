<?php

namespace Ilias\Maestro\Abstract;

use Ilias\Maestro\Utils\Utils;

abstract class Sanitizable
{
  public static function getSanitizedName(): string
  {
    $spreadClassName = explode("\\", static::class);
    return Utils::sanitizeForPostgres($spreadClassName[count($spreadClassName) - 1]);
  }
}