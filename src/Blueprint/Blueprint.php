<?php

use Ilias\Maestro\Database\Expression;

class Blueprint
{
    public readonly string $name;
    public readonly string $schema;
    /**
     * @var Column[] $columns An array to store the columns for the blueprint.
     */
    private array $columns = [];
    private Column $primaryKey;
    private array $foreignKeys = [];

    public function __construct(string $name, string $schema = 'public')
    {
        $this->name   = $name;
        $this->schema = $schema;
    }

    public function column(string $name): Column
    {
        return $this->columns[$name];
    }

    protected function createColumn(string $name, Type $type): Column
    {
        if (isset($this->columns[$name])) {
            return $this->columns[$name];
        }
        $column               = new Column($this, $this->schema, $name, $type);
        $this->columns[$name] = ['type' => $type];

        return $column;
    }

    public function primary(string $name, Type $type): Column
    {
        if (!empty($this->primaryKey)) {
            throw new Exception('Primary key already defined.');
        }
        $column = $this->createColumn($name, $type);

        $this->primaryKey = $column;

        return $column;
    }

    public function id(string $name = 'id'): Column
    {
        if (!empty($this->primaryKey)) {
            throw new Exception('Primary key already defined.');
        }
        $column = $this->primary($name, Type::SERIAL)->required();

        return $column;
    }

    public function uuid(string $name = 'uuid'): Column
    {
        if (!empty($this->primaryKey)) {
            throw new Exception('Primary key already defined.');
        }
        $column = $this->primary($name, Type::UUID)->required()->default(new Expression(Expression::RANDOM_UUID));

        return $column;
    }

    public function integer(string $name): Column
    {
        return $this->createColumn($name, Type::INTEGER);
    }

    public function text(string $name): Column
    {
        return $this->createColumn($name, Type::TEXT);
    }

    public function boolean(string $name): Column
    {
        return $this->createColumn($name, Type::BOOLEAN);
    }

    public function timestamp(string $name): Column
    {
        return $this->createColumn($name, Type::TIMESTAMP);
    }

    public function reference(Column $column): void
    {
        $this->foreignKeys[$column->name] = $column;
    }

    public function timestamps(): void
    {
        $this->timestamp('created_at')->default(new Expression(Expression::CURRENT_TIMESTAMP));
        $this->timestamp('updated_at');
    }

    public function softDelete(): void
    {
        $this->boolean('active')->default(true);
        $this->timestamp('deleted_at');
    }
}
