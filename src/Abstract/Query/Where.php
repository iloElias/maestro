<?php

namespace Ilias\Maestro\Abstract\Query;

/**
 * Represents a WHERE clause condition in a query.
 * This can be extended for database-specific implementations if needed.
 */
class Where
{
    public const EQUALS = '=';
    public const NOT_EQUAL = '!=';
    public const GREATER_THAN = '>';
    public const LESS_THAN = '<';
    public const GREATER_THAN_OR_EQUAL = '>=';
    public const LESS_THAN_OR_EQUAL = '<=';
    public const LIKE = '*~';
    public const NOT_LIKE = '!~';
    public const NOT_LIKE_OR_EQUAL = '<>';

    /**
     * The column name.
     *
     * @var string
     */
    private string $column;

    /**
     * The comparison operator.
     *
     * @var string
     */
    private string $operator;

    /**
     * The value to compare against.
     *
     * @var mixed
     */
    private mixed $value;

    /**
     * Creates a new Where condition.
     *
     * @param string $column          The column name
     * @param string $operator        The comparison operator (=, !=, >, <, etc.)
     * @param mixed  $value           The value to compare
     */
    public function __construct(
        string $column,
        string $operator,
        mixed $value,
    ) {
        $this->column = $column;
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * Gets the column name.
     *
     * @return string
     */
    public function column(): string
    {
        return $this->column;
    }

    /**
     * Gets the comparison operator.
     *
     * @return string
     */
    public function operator(): string
    {
        return $this->operator;
    }

    /**
     * Gets the value.
     *
     * @return mixed
     */
    public function value(): mixed
    {
        return $this->value;
    }
}

