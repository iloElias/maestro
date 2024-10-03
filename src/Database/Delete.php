<?php

namespace Ilias\Maestro\Database;

use Ilias\Maestro\Abstract\Query;
use Ilias\Maestro\Utils\Utils;

class Delete extends Query
{
  private string $table;
  public function from(string $table): Delete
  {
    $this->table = $this->validateTableName($table);
    return $this;
  }

  public function getSql(): string
  {
    $whereClause = implode(" AND ", $this->where);

    return "DELETE FROM {$this->table}" . ($whereClause ? " WHERE {$whereClause}" : "");
  }
}
