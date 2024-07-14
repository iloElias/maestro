<?php

namespace Ilias\Maestro\TestClasses;

use Ilias\Maestro\Abstract\Schema;

final class Hr extends Schema
{
  public User $user;
  public ShoppingCart $shoppingCart;
  public UserShoppingCart $userShoppingCar;
}
