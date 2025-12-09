<?php

namespace Ilias\Maestro\Database;

/**
 * Represents a raw SQL expression that should be inserted directly into queries
 * without parameter binding or escaping.
 */
class Expression
{
    public const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';
    public const CURRENT_DATE = 'CURRENT_DATE';
    public const CURRENT_TIME = 'CURRENT_TIME';
    public const NOW = 'NOW()';

    /**
     * The raw SQL expression.
     *
     * @var string
     */
    private string $expression;

    /**
     * Creates a new Expression instance.
     *
     * @param string $expression The raw SQL expression
     */
    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    /**
     * Returns the raw SQL expression as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->expression;
    }

    /**
     * Creates an expression for CURRENT_TIMESTAMP.
     *
     * @return Expression
     */
    public static function currentTimestamp(): Expression
    {
        return new self(self::CURRENT_TIMESTAMP);
    }

    /**
     * Creates an expression for NOW().
     *
     * @return Expression
     */
    public static function now(): Expression
    {
        return new self(self::NOW);
    }
}

