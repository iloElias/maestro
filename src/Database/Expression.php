<?php

namespace Ilias\Maestro\Database;

class Expression
{
    public const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';
    public const NOW               = 'NOW()';
    public const TRUE              = 'TRUE';
    public const FALSE             = 'FALSE';
    public const NULL              = 'NULL';
    public const DEFAULT           = 'DEFAULT';
    public const CURRENT_DATE      = 'CURRENT_DATE';
    public const CURRENT_TIME      = 'CURRENT_TIME';
    public const LOCALTIME         = 'LOCALTIME';
    public const LOCALTIMESTAMP    = 'LOCALTIMESTAMP';
    public const RANDOM_UUID       = 'gen_random_uuid()';

    public const DEFAULT_REPLACE_EXPRESSIONS = [
      self::RANDOM_UUID,
    ];

    public function __construct(
        private string $expression,
    ) {
    }

    public function __toString(): string
    {
        return $this->expression;
    }
}
