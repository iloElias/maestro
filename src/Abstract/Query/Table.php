<?php

namespace Ilias\Maestro\Query;

class Table
{
    /**
     * Summary of tables.
     *
     * @var Table[] where the keys are the table aliases
     */
    private static array $tables = [];

    protected string $schema;
    protected string $name;
    protected string $alias;

    public function __construct(string $name, ?string $alias = null)
    {
        $parts = explode('.', $name);

        $this->name = array_pop($parts);
        $this->schema = array_shift($parts) ?? 'public';

        $this->alias = self::makeAlias($alias);

        self::$tables[$this->alias] = $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function alias(): string
    {
        return $this->alias;
    }

    private function makeAlias(?string $alias = null)
    {
        if (!empty($alias)) {
            return $alias;
        }

        $parts = [];

        // TODO: abstract this to its own method
        if (!empty($this->schema)) {
            $aliasPart = '';
            foreach (explode('_', $this->schema) as $sp) {
                $aliasPart .= substr($sp, 0, 1);
            }
            $parts[] = $aliasPart;
        }

        $aliasPart = '';
        foreach (explode('_', $this->name) as $sp) {
            $aliasPart .= substr($sp, 0, 1);
        }
        $parts[] = $aliasPart;

        return implode('', $parts);
    }
}
