<?php

namespace Ilias\Maestro\Abstract\Query;

class WhereGroup
{
    public const AND = 'AND';
    public const OR = 'OR';

    protected string $operation = self::AND;
    /**
     * Summary of where.
     *
     * @var Where[]
     */
    protected array $where;

    /**
     * Summary of __construct.
     *
     * @param Where[] $where
     */
    public function __construct(array $where, string $operation = self::AND)
    {
        $this->where = $where;
        $this->operation = $operation;
    }

    /**
     * Summary of addWhere.
     *
     * @param Where $where
     *
     * @return void
     */
    public function add(array $where)
    {
        $this->where[] = $where;
    }

    /**
     * Summary of operation.
     */
    public function operation(): string
    {
        return $this->operation;
    }

    /**
     * Summary of where.
     *
     * @return Where[]
     */
    public function where(): array
    {
        return $this->where;
    }
}
