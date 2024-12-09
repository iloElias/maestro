<?php

class Column
{
    public readonly string $schema;
    public readonly string $name;
    protected Type $type;
    protected bool $nullable = true;
    protected bool $unique   = false;
    protected bool $primary  = false;
    protected mixed $default;
    protected Blueprint $blueprint;
    protected Column $reference;

    public function __construct(Blueprint $blueprint, string $schema = 'public', string $name, Type $type)
    {
        $this->blueprint = $blueprint;
        $this->schema    = $schema;
        $this->name      = $name;
        $this->type      = $type;
    }

    public function nullable(): self
    {
        $this->nullable = true;

        return $this;
    }

    public function required(): self
    {
        $this->nullable = false;

        return $this;
    }

    public function unique(): self
    {
        $this->unique = true;

        return $this;
    }

    public function primary(bool $primary = true): self
    {
        $this->primary  = $primary;
        $this->unique   = true;
        $this->nullable = false;

        return $this;
    }

    public function default(mixed $default): self
    {
        $this->default = $default;

        return $this;
    }

    public function references(Column $column): self
    {
        $this->reference = $column;

        return $this;
    }

    private function toArray(): array
    {
        return [
            'name'     => $this->name,
            'type'     => $this->type,
            'nullable' => $this->nullable,
            'unique'   => $this->unique,
            'primary'  => $this->primary,
            'default'  => $this->default,
        ];
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function __get(string $name): mixed
    {
        return $this->toArray()[$name];
    }
}
