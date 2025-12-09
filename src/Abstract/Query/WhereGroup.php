<?php

namespace Ilias\Maestro\Abstract\Query;

/**
 * 
 */
class WhereGroup
{
    public const AND = 'AND';
    public const OR = 'OR';

    protected string $operation = self::AND;
    /**
     * Summary of where
     * @var Where[]
     */
    protected array $where;

    /**
     * Summary of __construct
     * @param array $where
     * @param mixed $operation
     */
    public function __construct(array $where, $operation = self::AND)
    {
        $this->where = $where;
        $this->operation = $operation;
    }
}

