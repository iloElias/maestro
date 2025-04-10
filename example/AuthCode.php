<?php

namespace Maestro\Example;

use Blueprint;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Database\Expression;

final class AuthCode extends TrackableTable
{
    public Hr $schema;
    public int $id;
    public int $userId;
    public string $code;

    public static function compose(
        Blueprint $blueprint,
    ): Blueprint {
        $blueprint->id()->primary();
        $blueprint->integer('user_id')->required()->references(User::column('id'));
        $blueprint->text('code')->required()->default(new Expression('generate_four_digit_auth_code()'));

        return $blueprint;
    }
}
