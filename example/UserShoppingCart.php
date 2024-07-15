<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Schema;
use Ilias\Maestro\Abstract\Table;

final class UserShoppingCart extends Table
{
  public Store $schema;
  public User $userId;
  public ShoppingCart $shoppingCartId;

  public function __construct(
    User $userId,
    ShoppingCart $shoppingCartId
  ) {
    $this->userId = $userId;
    $this->shoppingCartId = $shoppingCartId;
  }
}
