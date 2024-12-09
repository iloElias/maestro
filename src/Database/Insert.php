<?php

namespace Ilias\Maestro\Database;

use Ilias\Maestro\Abstract\Query;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Utils\Utils;

class Insert extends Query
{
    private $table     = '';
    private $columns   = [];
    private $values    = [];
    private $returning = [];

    public function into(string $table): Insert
    {
        $this->table = $this->validateTableName($table);

        return $this;
    }

    private function registerValue($column, $value)
    {
        $column          = Utils::sanitizeForPostgres($column);
        $this->columns[] = $column;
        $paramName       = ":$column";
        $this->storeParameter($paramName, $value);
        $this->values[] = $paramName;
    }

    /**
     * Sets the values to be inserted into the database table.
     * This method accepts either an instance of the Table class or an associative array where the keys are column names and the values are the corresponding values to be inserted. It registers each value for the corresponding column.
     *
     * @param Table|array $data An instance of the Table class or an associative array of column-value pairs.
     *
     * @return Insert Returns the current instance of the Insert class for method chaining.
     */
    public function values(Table|array $data): Insert
    {
        foreach ((array) $data as $column => $value) {
            $this->registerValue($column, $value);
        }

        return $this;
    }

    /**
     * @deprecated This method does not make anything when used. Since you're building a insert query
     *
     * @param array $conditions An associative array of conditions for the WHERE clause.
     *
     * @return Insert Returns the current Insert instance.
     */
    public function where(string|array $conditions, string $operation = Select::AND, string $compaction = Select::EQUALS, bool $group = false): static
    {
        return $this;
    }

    /**
     * @deprecated This method does not make anything when used. Since you're building a insert query
     *
     * @param array $conditions An associative array of conditions for the WHERE clause.
     *
     * @return Insert Returns the current Insert instance.
     */
    public function in(string|array $conditions, string $operation = \Ilias\Maestro\Database\Select::AND, bool $group = false): static
    {
        return $this;
    }

    /**
     * Adds the specified columns to the list of columns to be returned after the insert operation.
     *
     * @param array $columns An array of column names to be added to the returning list.
     *
     * @return Insert Returns the current instance of the Insert class.
     */
    public function returning(array $columns): Insert
    {
        foreach ($columns as $column) {
            if (!in_array($column, $this->returning)) {
                $this->returning[] = $column;
            }
        }

        return $this;
    }

    public function getSql(): string
    {
        $sql = [];

        if (!empty($this->table) && !empty($this->columns) && !empty($this->values)) {
            $sql[] = 'INSERT INTO ' . $this->table;
            $sql[] = '(' . implode(', ', $this->columns) . ')';
            $sql[] = 'VALUES (' . implode(', ', $this->values) . ')';
        }

        if (!empty($this->returning)) {
            $sql[] = 'RETURNING ' . implode(', ', $this->returning);
        }

        return implode(' ', $sql);
    }
}
