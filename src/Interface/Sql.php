<?php

namespace Ilias\Maestro\Interface;

interface Sql
{
  public function getSql(): string;
  public function getParameters(): array;
}