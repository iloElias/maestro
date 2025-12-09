<?php

namespace Ilias\Maestro\Abstract\Query;

use Ilias\Maestro\Query\Column;
use Ilias\Maestro\Query\Table;

class Select
{
    public const STAR = '*';

    /**
     * The columns to select.
     *
     * @var Column[]
     */
    private array $columns = [];
    /**
     * The table to select from.
     */
    private Table $from;
    /**
     * The alias of the table.
     *
     * @var Join[]
     */
    private array $joins = [];
    /**
     * The query conditions.
     *
     * @var (Where|WhereGroup)[]
     */
    private array $where = [];
    /**
     * The group by columns.
     *
     * @var string[]
     */
    private array $group = [];
    /**
     * The having conditions.
     *
     * @var string[]
     */
    private array $having = [];
    /**
     * The order by columns.
     *
     * @var string[]
     */
    private array $order = [];
    /**
     * The offset to start from.
     */
    private int $offset = 0;
    /**
     * The limit to apply.
     */
    private int $limit = 0;
    /**
     * The distinct flag.
     */
    private bool $distinct = false;

    /**
     * Summary of __construct.
     *
     * @param string[] $columns
     */
    public function __construct(
        array $columns = [self::STAR],
    ) {
        foreach ($columns as $alias => $name) {
            $this->columns[] = new Column($name, $alias);
        }
    }

    public function from(string $from): Select
    {
        $this->from = new Table($from);

        return $this;
    }

    public function join(string $join): Select
    {
        $this->joins[] = $join;

        return $this;
    }

    /**
     * Summary of where.
     *
     * @param string|string[] $where
     * 
     * @throws \InvalidArgumentException
     */
    public function where(string|array $where): Select
    {
        if (is_string($where)) {
            $this->where[] = Where::string($where);
        }

        if (is_array($where)) {
            $this->where[] = Where::array($where);
        }

        return $this;
    }

    public function whereOr(array $where, bool $or = false, ?string $key = null): Select
    {
        $wheres = [];

        foreach ($where as $value) {
            if (is_string($value)) {
                $wheres[] = Where::string($value);
            }

            if (is_array($value)) {
                $wheres[] = Where::array($value);
            }
        }

        $this->where[] = new WhereGroup($wheres, WhereGroup::OR);

        return $this;
    }

    /**
     * Summary of whereGroup
     * @param Where[] $wheres
     * @param bool $or
     * @param ?string $key
     * @return Select
     */
    protected function whereGroup(array $where, bool $or = false, ?string $key = null): Select
    {
        $whereGroup = new WhereGroup($where, $or ? WhereGroup::OR : WhereGroup::AND);

        if (is_string($key)) {
            $this->where[$key] = $whereGroup;
            return $this;
        }

        if (is_array($where)) {
            $this->where[] = $whereGroup;
        }

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
