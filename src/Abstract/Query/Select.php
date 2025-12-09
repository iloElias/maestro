<?php

namespace Ilias\Maestro\Abstract\Query;

use Ilias\Maestro\Database\Expression;
use Src\Abstract\Query\Column;

class Select
{
    public const STAR = '*';

    /**
     * The columns to select.
     * @var Column[]
     */
    private array $columns = [];
    /**
     * The table to select from.
     * @var string
     */
    private string $from;
    /**
     * The alias of the table.
     * @var Join[]
     */
    private array $joins = [];
    /**
     * The query conditions.
     * @var (Where|WhereGroup)[]
     */
    private array $where = [];
    /**
     * The group by columns.
     * @var string[]
     */
    private array $group = [];
    /**
     * The having conditions.
     * @var string[]
     */
    private array $having = [];
    /**
     * The order by columns.
     * @var string[]
     */
    private array $order = [];
    /**
     * The offset to start from.
     * @var int
     */
    private int $offset = 0;
    /**
     * The limit to apply.
     * @var int
     */
    private int $limit = 0;
    /**
     * The distinct flag.
     * @var bool
     */
    private bool $distinct = false;

    /**
     * Summary of __construct
     * @param string[] $column
     */
    public function __construct(
        array $column = [self::STAR],
    ) {
        foreach ($column as $alias => $name) {
            $this->columns[] = new Column($name, $alias);
        }
    }

    public function from(string $from): Select
    {
        $this->from = $from;

        return $this;
    }

    public function join(string $join): Select
    {
        $this->joins[] = $join;

        return $this;
    }

    public function group(array $group): Select
    {
        $this->group = $group;

        return $this;
    }

    public function having(string $having): Select
    {
        $this->having[] = $having;

        return $this;
    }
}