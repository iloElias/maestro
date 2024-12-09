<?php

namespace Maestro\Example;

use Blueprint;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Database\Expression;
use Ilias\Maestro\Types\Serial;
use Ilias\Maestro\Types\Unique;

final class User extends TrackableTable
{
    public Hr $schema;
    /** @primary */
    public Serial $id;
    /** @primary
     * @not_nuable */
    public Unique|Expression|string $uuid = Expression::RANDOM_UUID;
    /** @not_nuable */
    public string $name;
    /** @not_nuable */
    public string $surname;
    /** @unique */
    public string $nickname;
    /** @unique */
    public string $email;
    public string $password;

    public static function compose(
        Blueprint $blueprint,
    ): Blueprint {
        $blueprint->id()->primary();
        $blueprint->text('name')->required();
        $blueprint->text('surname')->required();
        $blueprint->text('nickname')->required()->unique();
        $blueprint->text('email')->required()->unique();
        $blueprint->text('password')->required();

        return $blueprint;
    }
}
