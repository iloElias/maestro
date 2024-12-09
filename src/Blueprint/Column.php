<?php

class Column
{
  public readonly string $schema;
  public readonly string $name;
  protected Type $type;
  protected bool $nullable;
  protected bool $unique;
  protected bool $primary;
  protected mixed $default;
  protected Column $reference;

  public function __construct(string $schema, string $name, Type $type)
  {
    $this->schema = $schema;
    $this->name = $name;
    $this->type = $type;
  }

  public function nullable(bool $nullable = true): self
  {
    $this->nullable = $nullable;
    return $this;
  }

  public function unique(bool $unique = true): self
  {
    $this->unique = $unique;
    return $this;
  }

  public function primary(bool $primary = true): self
  {
    $this->primary = $primary;
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
      'name' => $this->name,
      'type' => $this->type,
      'nullable' => $this->nullable,
      'unique' => $this->unique,
      'primary' => $this->primary,
      'default' => $this->default,
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
