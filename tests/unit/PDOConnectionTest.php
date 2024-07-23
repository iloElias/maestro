<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ilias\Maestro\Database\PDOConnection;

class PDOConnectionTest extends TestCase
{
  public function testGetInstanceReturnsPdoInstance()
  {
    $pdo = PDOConnection::getInstance();
    $this->assertInstanceOf(\PDO::class, $pdo);
  }

  public function testGetInstanceReturnsSameInstance()
  {
    $pdo1 = PDOConnection::getInstance();
    $pdo2 = PDOConnection::getInstance();
    $this->assertSame($pdo1, $pdo2);
  }
}
