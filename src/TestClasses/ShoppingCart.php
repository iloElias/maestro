<?php

namespace Ilias\Maestro\TestClasses;

use Ilias\Maestro\Abstract\Schema;
use Ilias\Maestro\Abstract\Table;

final class ShoppingCart extends Table
{
  public Hr $schema;
  public float $total;
  public array $items;

  public function __construct(
    float $total,
    array $items
  ) {
    $this->total = $total;
    $this->items = $items;
  }
}
