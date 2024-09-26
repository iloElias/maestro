<?php

namespace Maestro\Example;
use Ilias\Maestro\Abstract\Schema;
use Ilias\Maestro\Types\Serial;

final class Social extends Schema
{
  public Serial $id;
  public Post $post;
  public TaggedUser $taggedUser;
}