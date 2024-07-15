<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Table;

final class Item extends Table
{
  public Store $schema;
  public string $name;
  public string $description;
  public float $price;

  public function __construct(
    string $name,
    string $description,
    float $price
  ) {
    $this->name = $name;
    $this->description = $description;
    $this->price = $price;
  }
}
