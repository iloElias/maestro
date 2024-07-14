<?php

namespace Ilias\Maestro\Database;

use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Interface\Sql;

class Insert implements Sql
{
  private $table = '';
  private $columns = [];
  private $values = [];
  private $parameters = [];

  public function into(string $table)
  {
    try {
      $this->table = call_user_func($table . "::getTableSchemaAddress");
    } catch (\Throwable) {
      $this->table = $table;
    }
    
    return $this;
  }

  public function values(Table | array $data)
  {
    if (is_subclass_of($data, Table::class)) {
      $tableColumns = $data::getColumns();
      foreach ($tableColumns as $column => $type) {
        $this->columns[] = $column;
        $this->values[] = ":$column";
        $this->parameters[$column] = $data->$column;
      }
    } elseif (is_array($data)) {
      foreach ($data as $column => $value) {
        $this->columns[] = $column;
        $this->values[] = ":$column";
        $this->parameters[$column] = $value;
      }
    }
    return $this;
  }

  public function getSql(): string
  {
    $sql = [];

    if (!empty($this->table) && !empty($this->columns) && !empty($this->values)) {
      $sql[] = "INSERT INTO " . $this->table;
      $sql[] = "(" . implode(", ", $this->columns) . ")";
      $sql[] = "VALUES (" . implode(", ", $this->values) . ")";
    }

    return implode(" ", $sql);
  }

  public function getParameters(): array
  {
    return $this->parameters;
  }
}
