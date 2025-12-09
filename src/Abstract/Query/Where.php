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
     */
    private string $column;

    /**
     * The comparison operator.
     */
    private string $operator;

    /**
     * The value to compare against.
     */
    private mixed $value;

    /**
     * Creates a new Where condition.
     *
     * @param string $column   The column name
     * @param string $operator The comparison operator (=, !=, >, <, etc.)
     * @param mixed  $value    The value to compare
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
     */
    public function column(): string
    {
        return $this->column;
    }

    /**
     * Gets the comparison operator.
     */
    public function operator(): string
    {
        return $this->operator;
    }

    /**
     * Gets the value.
     */
    public function value(): mixed
    {
        return $this->value;
    }

    public static function string(string $where): Where
    {
        $operator = self::operation($where);

        $parts = explode($operator, $where, 2);
        $column = trim($parts[0] ?? '');
        $value = trim($parts[1] ?? '');

        return new self($column, $operator, $value);
    }

    public static function array(array $where): Where
    {
        $operator = self::operation($where);
        $column = array_shift($where);
        $value = array_pop($where);
        
        return new self($column, $operator, $value);
    }

    protected static function operation(string|array $where): string
    {
        $operators = [
            self::EQUALS,
            self::NOT_EQUAL,
            self::GREATER_THAN,
            self::LESS_THAN,
            self::GREATER_THAN_OR_EQUAL,
            self::LESS_THAN_OR_EQUAL,
            self::LIKE,
            self::NOT_LIKE,
            self::NOT_LIKE_OR_EQUAL,
        ];

        $foundOperator = null;
        if (is_string($where)) {
            foreach ($operators as $op) {
                $pattern = '/' . preg_quote($op, '/') . '/';
                if (preg_match($pattern, $where)) {
                    $foundOperator = $op;
                    break;
                }
            }
        }

        if ($foundOperator === null) {
            $foundOperator = self::EQUALS;
        }

        return $foundOperator;
    }

}
