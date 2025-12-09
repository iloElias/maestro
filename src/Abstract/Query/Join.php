<?php

namespace Ilias\Maestro\Abstract\Query;

/**
 * Represents a JOIN clause in a query.
 * This can be extended for database-specific implementations if needed.
 */
class Join
{
    public const INNER = 'INNER';
    public const LEFT = 'LEFT';
    public const RIGHT = 'RIGHT';
    public const FULL = 'FULL';

    /**
     * The table to join.
     *
     * @var string
     */
    private string $table;

    /**
     * The table alias.
     *
     * @var string|null
     */
    private ?string $alias;

    /**
     * The join condition (ON clause).
     *
     * @var string
     */
    private string $condition;

    /**
     * The type of join (INNER, LEFT, RIGHT, FULL).
     *
     * @var string
     */
    private string $type;

    /**
     * Creates a new Join clause.
     *
     * @param string      $table     The table name to join
     * @param string      $condition The join condition (ON clause)
     * @param string      $type      The type of join (INNER, LEFT, RIGHT, FULL)
     * @param string|null $alias     Optional table alias
     */
    public function __construct(
        string $table,
        string $condition,
        string $type = self::INNER,
        ?string $alias = null
    ) {
        $this->table = $table;
        $this->condition = $condition;
        $this->type = strtoupper($type);
        $this->alias = $alias;
    }

    /**
     * Gets the table name.
     *
     * @return string
     */
    public function table(): string
    {
        return $this->table;
    }

    /**
     * Gets the table alias.
     *
     * @return string|null
     */
    public function alias(): ?string
    {
        return $this->alias;
    }

    /**
     * Gets the join condition.
     *
     * @return string
     */
    public function condition(): string
    {
        return $this->condition;
    }

    /**
     * Gets the join type.
     *
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }
}

