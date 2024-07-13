<?php

namespace Ilias\Maestro\TestClasses;

use Ilias\Maestro\Abstract\Table;

final class User extends Table
{
  public string $username;
  public string $name;
  public string $password;
}
