<?php

namespace Ilias\Maestro\Database;

class Expression
{
  public function __construct(
    private string $expression,
  ) {
  }

  public function __tostring(): string
  {
    return $this->expression;
  }
}
