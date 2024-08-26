<?php

namespace Ilias\Maestro\Abstract;

abstract class Sql
{
  public abstract function getSql(): string;
  public abstract function getParameters(): array;
  public function bindParameters(): string
  {
    $params = $this->getParameters();
    $stmt = $this->getSql();
    foreach ($params as $key => $value) {
      $stmt = str_replace($key, $value, $stmt);
    }
    return $stmt;
  }
}