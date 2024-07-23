<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Table;

final class Post extends Table
{
  public Social $schema;
  public User $userId;
  public string $postContent;
}