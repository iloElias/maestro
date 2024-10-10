<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Database\Expression;
use Ilias\Maestro\Types\Serial;

final class AuthCode extends TrackableTable
{
  public Hr $schema;
  /** @primary */
  public Serial $id;
  /** @not_nuable */
  public User $userId;
  public string | Expression $code = 'generate_four_digit_auth_code()';

  public function compose(string $code)
  {
    $this->code = $code;
  }
}
