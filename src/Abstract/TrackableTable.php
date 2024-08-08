<?php

namespace Ilias\Maestro\Abstract;

use DateTime;
use Ilias\Maestro\Interface\PostgresFunction;

abstract class TrackableTable extends Table
{
  public bool $active = true;
  public DateTime | PostgresFunction | string $createdIn = "CURRENT_TIMESTAMP";
  public DateTime $updatedIn;
  public DateTime $inactivatedIn;
}
