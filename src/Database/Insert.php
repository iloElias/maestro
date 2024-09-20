<?php

namespace Ilias\Maestro\Database;

use Ilias\Maestro\Abstract\Bindable;
use Ilias\Maestro\Abstract\Table;

class Insert extends Bindable
{
  private $table = '';
  private $columns = [];
  private $values = [];

  public function into(string $table): Insert
  {
    $this->table = $this->validateTableName($table);
    return $this;
  }

  public function values(Table|array $data): Insert
  {
    if (is_subclass_of($data, Table::class)) {
      $tableColumns = $data::getColumns();
      foreach ($tableColumns as $column => $type) {
        $this->columns[] = $column;
        $paramName = ":$column";
        $this->values[] = $paramName;
        $this->parameters[$paramName] = $data->$column;
      }
    } elseif (is_array($data)) {
      foreach ($data as $column => $value) {
        $this->columns[] = $column;
        $paramName = ":$column";
        $this->values[] = $paramName;
        $this->parameters[$paramName] = $value;
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
