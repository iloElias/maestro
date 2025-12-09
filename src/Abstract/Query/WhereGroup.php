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
    protected array $wheres;

    /**
     * Summary of __construct.
     *
     * @param Where[] $wheres
     */
    public function __construct(array $wheres, string $operation = self::AND)
    {
        $this->wheres = $wheres;
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
        $this->wheres[] = $where;
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
        return $this->wheres;
    }
}
