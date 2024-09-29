<?php

namespace Ilias\Maestro\Database;

class DatabaseFunction
{
  private string $name;
  private string $returnType;
  private string $sqlDefinition;

  public function __construct(string $name, string $returnType, string $sqlDefinition)
  {
    $this->name = $name;
    $this->returnType = $returnType;
    $this->sqlDefinition = $sqlDefinition;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getReturnType(): string
  {
    return $this->returnType;
  }

  public function getSqlDefinition(): string
  {
    return $this->sqlDefinition;
  }
}