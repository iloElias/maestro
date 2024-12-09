<?php

namespace Ilias\Maestro\Types;

use Ilias\Maestro\Abstract\Identifier;

class Unique extends Identifier
{
    public static function tableIdentifierType(): string
    {
        return Postgres::UUID;
    }

    public static function tableIdentifierReferenceType(): string
    {
        return Postgres::TEXT;
    }
}
