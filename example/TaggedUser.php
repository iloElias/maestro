<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Table;

final class TaggedUser extends Table
{
  public Social $schema;
  public Post $postId;
  public User $userId;
}