<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Types\Serial;

final class TaggedUser extends Table
{
  public Serial $id;
  public Social $schema;
  public Post $postId;
  public User $userId;
}