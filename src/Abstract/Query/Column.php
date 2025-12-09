<?php

namespace Src\Abstract\Query;

class Column
{
    protected string $name;
    protected ?string $alias = null;
    public function __construct(
        string $name,
        ?string $alias
    ) {
        $this->name = $name;
        if (!is_numeric($alias)) {
            $this->alias = $alias;
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function alias(): string
    {
        return $this->alias;
    }
}
