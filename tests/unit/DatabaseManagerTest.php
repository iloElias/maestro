<?php

namespace Tests\Unit;

use Maestro\Example\MarkfyDb;
use PHPUnit\Framework\TestCase;
use Ilias\Maestro\Core\Manager;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Database\Update;
use Maestro\Example\Hr;
use Maestro\Example\TaggedUser;
use Maestro\Example\User;
use PDO;

class DatabaseManagerTest extends TestCase
{
  private $pdo;
  private $manager;

  protected function setUp(): void
  {
    $this->pdo = $this->createMock(PDO::class);
    $this->manager = new Manager();
  }

  public function testCreateDatabase()
  {
    $database = new MarkfyDb();
    $sql = $this->manager->createDatabase($database);

    $expectedSql = [
      "CREATE SCHEMA IF NOT EXISTS \"hr\";",
      "CREATE SCHEMA IF NOT EXISTS \"social\";",
      "CREATE TABLE IF NOT EXISTS \"hr\".\"user\" (\n\tid SERIAL PRIMARY KEY,\n\tnickname TEXT NOT NULL UNIQUE,\n\temail TEXT NOT NULL UNIQUE,\n\tpassword TEXT NOT NULL,\n\tactive BOOLEAN NOT NULL DEFAULT TRUE,\n\tcreated_in TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n\tupdated_in TIMESTAMP NULL,\n\tinactivated_in TIMESTAMP NULL\n);",
      "CREATE TABLE IF NOT EXISTS \"social\".\"post\" (\n\tid SERIAL PRIMARY KEY,\n\tuser_id INTEGER NULL,\n\tpost_content TEXT NULL\n);",
      "CREATE TABLE IF NOT EXISTS \"social\".\"tagged_user\" (\n\tid SERIAL PRIMARY KEY,\n\tpost_id INTEGER NULL,\n\tuser_id INTEGER NULL\n);",
      "ALTER TABLE \"social\".\"post\" ADD CONSTRAINT fk_post_user_id FOREIGN KEY (\"user_id\") REFERENCES \"hr\".\"user\"(\"id\");",
      "ALTER TABLE \"social\".\"tagged_user\" ADD CONSTRAINT fk_tagged_user_post_id FOREIGN KEY (\"post_id\") REFERENCES \"social\".\"post\"(\"id\");",
      "ALTER TABLE \"social\".\"tagged_user\" ADD CONSTRAINT fk_tagged_user_user_id FOREIGN KEY (\"user_id\") REFERENCES \"hr\".\"user\"(\"id\");"
    ];

    $this->assertEquals($expectedSql, $sql);
  }

  public function testCreateTablesForSchema()
  {
    $schema = new Hr();
    $sql = $this->manager->createTablesForSchema($schema);

    $expectedSql = [
      "CREATE TABLE IF NOT EXISTS \"hr\".\"user\" (\n\tid SERIAL PRIMARY KEY,\n\tnickname TEXT NOT NULL UNIQUE,\n\temail TEXT NOT NULL UNIQUE,\n\tpassword TEXT NOT NULL,\n\tactive BOOLEAN NOT NULL DEFAULT TRUE,\n\tcreated_in TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n\tupdated_in TIMESTAMP NULL,\n\tinactivated_in TIMESTAMP NULL\n);"
    ];

    $this->assertEquals($expectedSql, $sql);
  }

  public function testCreateTable()
  {
    $table = User::class;
    $sql = $this->manager->createTable($table);

    $expectedSql = "CREATE TABLE IF NOT EXISTS \"hr\".\"user\" (\n\tid SERIAL PRIMARY KEY,\n\tnickname TEXT NOT NULL UNIQUE,\n\temail TEXT NOT NULL UNIQUE,\n\tpassword TEXT NOT NULL,\n\tactive BOOLEAN NOT NULL DEFAULT TRUE,\n\tcreated_in TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n\tupdated_in TIMESTAMP NULL,\n\tinactivated_in TIMESTAMP NULL\n);";

    $this->assertEquals($expectedSql, $sql);
  }

  public function testCreateSchema()
  {
    $sql = $this->manager->createSchema(Hr::class);

    $expectedSql = "CREATE SCHEMA IF NOT EXISTS \"hr\";";
    $this->assertEquals($expectedSql, $sql);
  }

  public function testCreateForeignKeyConstraints()
  {
    $table = TaggedUser::class;
    $constraints = $this->manager->createForeignKeyConstraints($table);

    $expectedConstraints = [
      "ALTER TABLE \"social\".\"tagged_user\" ADD CONSTRAINT fk_tagged_user_post_id FOREIGN KEY (\"post_id\") REFERENCES \"social\".\"post\"(\"id\");",
      "ALTER TABLE \"social\".\"tagged_user\" ADD CONSTRAINT fk_tagged_user_user_id FOREIGN KEY (\"user_id\") REFERENCES \"hr\".\"user\"(\"id\");"
    ];

    $this->assertEquals($expectedConstraints, $constraints);
  }

  public function testGetSchemaNameFromTable()
  {
    $table = User::class;
    $schemaName = $this->manager->getSchemaNameFromTable($table);

    $expectedSchemaName = "hr";
    $this->assertEquals($expectedSchemaName, $schemaName);
  }

  public function testIsPropertyNotNull()
  {
    $reflectionClass = new \ReflectionClass(User::class);
    $propertyName = "nickname";

    $isNotNull = $this->manager->isPropertyNotNull($reflectionClass, $propertyName);
    $this->assertTrue($isNotNull);
  }

  public function testGetPropertyDefaultValue()
  {
    $reflectionClass = new \ReflectionClass(User::class);
    $propertyName = "createdIn";

    $defaultValue = $this->manager->getPropertyDefaultValue($reflectionClass, $propertyName);
    $this->assertEquals("CURRENT_TIMESTAMP", $defaultValue);
  }

  public function testFormatDefaultValue()
  {
    $stringValue = "test";
    $booleanValueTrue = true;
    $booleanValueFalse = false;
    $intValue = 123;

    $this->assertEquals("'test'", $this->manager->formatDefaultValue($stringValue));
    $this->assertEquals("TRUE", $this->manager->formatDefaultValue($booleanValueTrue));
    $this->assertEquals("FALSE", $this->manager->formatDefaultValue($booleanValueFalse));
    $this->assertEquals("123", $this->manager->formatDefaultValue($intValue));
  }

  public function testGetColumnType()
  {
    $type = "string";
    $expectedColumnType = "TEXT";

    $this->assertEquals($expectedColumnType, $this->manager->getColumnType($type));
  }

  public function testInsertIntoTable()
  {
    $table = new User("nickname", "email", "password", true, new \DateTime());
    $data = ["nickname" => "nickname", "email" => "email", "password" => "password", "active" => true, "createdIn" => new \DateTime()];

    $insert = $this->manager->insertIntoTable($table, $data);

    $this->assertInstanceOf(Insert::class, $insert);
  }

  public function testUpdateTable()
  {
    $table = new User("nickname", "email", "password", true, new \DateTime());
    $data = ["nickname" => "updated_nickname"];
    $conditions = ["email" => "email"];

    $update = $this->manager->updateTable($table, $data, $conditions);

    $this->assertInstanceOf(Update::class, $update);
    $this->assertEquals("UPDATE user SET nickname = :nickname WHERE email = :email", $update->getSql());
    $this->assertEquals(['nickname' => 'updated_nickname', 'email' => 'email'], $update->getParameters());
  }


  public function testSelectFromTable()
  {
    $table = new User("nickname", "email", "password", true, new \DateTime());
    $columns = ["nickname", "email"];
    $conditions = ["active" => true];

    $select = $this->manager->selectFromTable($table, $columns, $conditions);

    $this->assertInstanceOf(Select::class, $select);
    $this->assertEquals("SELECT nickname, email FROM user WHERE active = :active", $select->getSql());
    $this->assertEquals(['active' => true], $select->getParameters());
  }

  public function testExecuteQuery()
  {
    $sql = "SELECT * FROM users";
    $this->pdo->method('exec')->willReturn(1);
    $stmt = $this->manager->executeQuery($this->pdo, $sql);

    $this->assertTrue($stmt);
  }
}


// <?php

// namespace Tests\Unit;

// use Ilias\Maestro\Core\Manager;
// use Maestro\Example\TaggedUser;
// use Maestro\Example\User;
// use PHPUnit\Framework\TestCase;

// use PDO;

// class DatabaseManagerTest extends TestCase
// {
//   private $pdo;
//   private $dbManager;

//   protected function setUp(): void
//   {
//     $this->pdo = $this->createMock(PDO::class);
//     $this->dbManager = new Manager();
//   }

//   public function testCreateTable()
//   {
//     $expectedSql = "CREATE TABLE IF NOT EXISTS \"hr\".\"user\" (\n\tid SERIAL PRIMARY KEY,\n\tnickname TEXT NOT NULL UNIQUE,\n\temail TEXT NOT NULL UNIQUE,\n\tpassword TEXT NOT NULL,\n\tactive BOOLEAN NOT NULL DEFAULT TRUE,\n\tcreated_in TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n\tupdated_in TIMESTAMP NULL,\n\tinactivated_in TIMESTAMP NULL\n);";

//     $sql = $this->dbManager->createTable(User::class);
//     $this->assertEquals($expectedSql, $sql);
//   }

//   public function testCreateForeignKeyConstraints()
//   {
//     $expectedConstraints = [
//       "ALTER TABLE \"social\".\"tagged_user\" ADD CONSTRAINT fk_tagged_user_post_id FOREIGN KEY (\"post_id\") REFERENCES \"social\".\"post\"(\"id\");",
//       "ALTER TABLE \"social\".\"tagged_user\" ADD CONSTRAINT fk_tagged_user_user_id FOREIGN KEY (\"user_id\") REFERENCES \"hr\".\"user\"(\"id\");",
//     ];

//     $constraints = $this->dbManager->createForeignKeyConstraints(TaggedUser::class);
//     $this->assertEquals($expectedConstraints, $constraints);
//   }

//   public function testGetSchemaNameFromTable()
//   {
//     $expectedSchemaName = 'hr';

//     $schemaName = $this->dbManager->getSchemaNameFromTable(User::class);
//     $this->assertEquals($expectedSchemaName, $schemaName);
//   }
// }
