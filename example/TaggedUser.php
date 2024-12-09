<?php

namespace Maestro\Example;

use Blueprint;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Types\Serial;

final class TaggedUser extends Table
{
    public Social $schema;
    public Serial|int $id;
    public Post|int $postId;
    public User|int $userId;

    public static function compose(
        Blueprint $blueprint,
    ): Blueprint {
        $blueprint->id()->primary();
        $blueprint->integer('post_id')->required()->references(Post::column('id'));
        $blueprint->integer('user_id')->required()->references(User::column('id'));

        return $blueprint;
    }
}
