<?php

namespace Maestro\Example;

use Blueprint;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Types\Serial;

final class Post extends Table
{
    public Social $schema;
    public Serial|int $id;
    public User|int $userId;
    public string $postContent;

    public static function compose(
        Blueprint $blueprint,
    ): Blueprint {
        $blueprint->id()->primary();
        $blueprint->integer('user_id')->required()->references(User::column('id'));
        $blueprint->text('post_content')->required();

        return $blueprint;
    }
}
