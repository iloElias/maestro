<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Table;

final class ItemCategory extends Table
{
  public Store $schema;
  public string $name;
  public string $description;

  public function __construct(
    string $name,
    string $description,
  ) {
    $this->name = $name;
    $this->description = $description;
  }
}
