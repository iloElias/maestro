<?php

namespace Maestro\Example;
use Ilias\Maestro\Abstract\Schema;
use Ilias\Maestro\Types\Postgres;

final class Hr extends Schema
{
  public User $user;
  public AuthCode $authCode;

  public function __construct()
  {
    self::declareFunction(
      'generate_four_digit_auth_code',
      Postgres::TEXT,
      'CREATE OR REPLACE FUNCTION generate_four_digit_auth_code() RETURNS TEXT AS $$ BEGIN RETURN CAST(FLOOR(1000 + RANDOM() * 9000) AS TEXT); END; $$ LANGUAGE plpgsql;'
    );
  }
}