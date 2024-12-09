<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Schema;

final class Social extends Schema
{
    public Post $post;
    public TaggedUser $taggedUser;
}
