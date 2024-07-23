<?php

namespace Maestro\Example;

use DateTime;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Interface\PostgresFunction;

final class User extends Table
{
  public Hr $schema;
  public string $nickname;
  public string $email;
  public string $password;
  public bool $active = true;
  public DateTime | PostgresFunction | string $createdIn = "CURRENT_TIMESTAMP";
  public DateTime $updatedIn;
  public DateTime $inactivatedIn;

  public function __construct(
    string $nickname,
    string $email,
    string $password,
    bool $active,
    DateTime $createdIn
  ) {
    $this->nickname = $nickname;
    $this->email = $email;
    $this->password = $password;
    $this->active = $active;
    $this->createdIn = $createdIn;
  }

  public static function getUniqueColumns(): array
  {
    return ["nickname", "email"];
  }
}
