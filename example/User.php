<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Interface\PostgresFunction;
use Ilias\Maestro\Types\Timestamp;

final class User extends Table
{
  public Hr $schema;
  /** @unique */
  public string $nickname;
  /** @unique */
  public string $email;
  public string $password;
  public bool $active = true;
  public Timestamp|PostgresFunction|string $createdIn = PostgresFunction::CURRENT_TIMESTAMP;
  public Timestamp $updatedIn;
  public Timestamp $inactivatedIn;

  public function __construct(
    string $nickname,
    string $email,
    string $password,
    bool $active,
    Timestamp $createdIn
  ) {
    $this->nickname = $nickname;
    $this->email = $email;
    $this->password = $password;
    $this->active = $active;
    $this->createdIn = $createdIn;
  }
}
