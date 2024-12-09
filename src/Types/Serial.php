<?php

namespace Ilias\Maestro\Types;

use Ilias\Maestro\Abstract\Identifier;

class Serial extends Identifier
{
    public static function tableIdentifierType(): string
    {
        return 'SERIAL';
    }

    public static function tableIdentifierReferenceType(): string
    {
        return Postgres::INTEGER;
    }
}
