<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Schema;

final class Store extends Schema
{
  public Store $schema;
  public Item $item;
  public ItemCategory $itemCategory;
  public ItemShoppingCart $itemShoppingCart;
  public ShoppingCart $shoppingCart;
  public UserShoppingCart $userShoppingCar;
}
