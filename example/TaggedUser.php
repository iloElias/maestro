<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Types\Serial;

final class TaggedUser extends Table
{
  public Social $schema;
  public Serial|int $id;
  public Post|int $postId;
  public User|int $userId;
}