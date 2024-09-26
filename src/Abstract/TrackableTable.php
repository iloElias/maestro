<?php

namespace Ilias\Maestro\Abstract;

use Ilias\Maestro\Database\Expression;
use Ilias\Maestro\Types\Timestamp;

abstract class TrackableTable extends Table
{
  public bool $active = true;
  public Timestamp | Expression | string $createdIn = Expression::CURRENT_TIMESTAMP;
  public Timestamp $updatedIn;
  public Timestamp $inactivatedIn;
}
