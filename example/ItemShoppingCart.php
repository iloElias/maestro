<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Table;

final class ItemShoppingCart extends Table
{
  public Store $schema;
  public Item $item;
  public ShoppingCart $shoppingCart;


  public function __construct(
    Item $item,
    ShoppingCart $shoppingCart,
  ) {
    $this->item = $item;
    $this->shoppingCart = $shoppingCart;
  }
}
