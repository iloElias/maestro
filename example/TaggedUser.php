<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Types\Serial;

final class TaggedUser extends Table
{
  public Social $schema;
  public Serial $id;
  public Post $postId;
  public User $userId;
}