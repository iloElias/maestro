<?php

namespace Ilias\Maestro\TestClasses;

use Ilias\Maestro\Abstract\Table;

final class ShoppingCar extends Table
{
  public float $total;
  public array $items;
}
