<?php

namespace Ilias\Maestro\TestClasses;

use Ilias\Maestro\Abstract\Table;

final class User extends Table
{
  public Hr $schema;
  public string $username;
  public string $name;
  public string $email;
  public string $password;

  public function __construct(
      string $username,
      string $name,
      string $email,
      string $password
  ) {
    $this->username = $username;
    $this->name = $name;
    $this->email = $email;
    $this->password = $password;
  }
}
