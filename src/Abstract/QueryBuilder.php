<?php

namespace Ilias\Maestro\Abstract;

use Ilias\Maestro\Interface\Sql;

abstract class QueryBuilder
{
  public function bindParameters(Sql $sql): void
  {
    $params = $sql->getParameters();
    $stmt = $sql->getSql();
    foreach ($params as $key => $value) {
      $stmt = str_replace($key, $value, $stmt);
    }
  }
}
