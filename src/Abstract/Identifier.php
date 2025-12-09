<?php

namespace Ilias\Maestro\Abstract;

abstract class Identifier
{
    public function __toString(): string
    {
        return self::tableIdentifierType();
    }

    abstract public static function tableIdentifierType(): string;

    abstract public static function tableIdentifierReferenceType(): string;
}
