<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Types\Serial;

final class Post extends Table
{
  public Serial $id;
  public Social $schema;
  public User $userId;
  public string $postContent;
}