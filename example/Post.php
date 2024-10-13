<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Types\Serial;

final class Post extends Table
{
  public Social $schema;
  public Serial $id;
  public User|int $userId;
  public string $postContent;
}