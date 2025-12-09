<?php

namespace Ilias\Maestro\Abstract\Query;

/**
 * Represents a IN clause condition in a query.
 * This can be extended for database-specific implementations if needed.
 */
class In
{
    /**
     * The column name.
     */
    private string $column;

    /**
     * The values to compare against.
     */
    private mixed $values;

    /**
     * Creates a new Where condition.
     *
     * @param string  $column The column name
     * @param mixed[] $values The values to compare
     */
    public function __construct(
        string $column,
        mixed $values,
    ) {
        $this->column = $column;
        $this->values = $values;
    }

    /**
     * Gets the column name.
     */
    public function column(): string
    {
        return $this->column;
    }

    /**
     * Gets the values.
     */
    public function values(): mixed
    {
        return $this->values;
    }
}
