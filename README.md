## Maestro Documentation
[![Maintainer](http://img.shields.io/badge/maintainer-@iloElias-blue.svg)](https://github.com/iloElias)
[![Maintainer](http://img.shields.io/badge/maintainer-@dhenriquearantes-blue.svg)](https://github.com/dhenriquearantes)
[![Package](https://img.shields.io/badge/package-iloelias/maestro-orange.svg)](https://packagist.org/packages/ilias/maestro)
[![Source Code](https://img.shields.io/badge/source-iloelias/maestro-blue.svg)](https://github.com/iloElias/maestro)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)

Maestro is a PHP library designed to facilitate the creation and management of PostgreSQL database schemas and tables. It allows developers to define schemas and tables using PHP classes, providing a clear and structured way to manage database definitions and relationships.

### Table of Contents

- [Introduction](#introduction)
- [Installation](#installation)
- [Schema and Table Classes](#schema-and-table-classes)
- [Defining Schemas and Tables](#defining-schemas-and-tables)
  - [Schema Class](#schema-class)
  - [Table Class](#table-class)
  - [Unique Columns](#unique-columns)
  - [Default Values](#default-values)
- [DatabaseManager](#databasemanager)
  - [Creating Schemas and Tables](#creating-schemas-and-tables)
  - [Foreign Key Constraints](#foreign-key-constraints)
  - [Executing Queries](#executing-queries)
- [Query Builders](#query-builders)
  - [Select](#select)
  - [Insert](#insert)
  - [Update](#update)
  - [Delete](#delete)
- [Examples](#examples)
  - [Defining a Schema and Tables](#defining-a-schema-and-tables)
  - [Generating SQL Queries](#generating-sql-queries)

### Installation

To install Maestro, use Composer:

```sh
composer require ilias/maestro
```

### Schema and Table Classes

Maestro uses abstract classes for schemas and tables. Developers extend these classes to define their own schemas and tables.

### Defining Schemas and Tables

#### Schema Class

A schema class extends the `Schema` abstract class. It contains table attributes that are typed with the table classes.

#### Table Class

A table class extends the `Table` abstract class. It can define columns as class properties, specifying their types and optional default values.

#### Unique Columns

You can specify columns that should be unique by overriding the `getUniqueColumns` method in your table class.
You can also define a column as unique by adding the `@unique` clause to the documentation of the attribute you want to make unique. This format will not work if the `getUniqueColumns` method is overridden.

#### Default Values

Columns can have default values. If a default value is a PostgreSQL function, it should be defined as a `PostgresFunction` type to ensure it is not quoted in the final SQL query.

### DatabaseManager

The `DatabaseManager` class provides methods to create schemas, tables, and manage foreign key constraints.

#### Creating Schemas and Tables

The `createSchema` and `createTable` methods generate SQL queries to create schemas and tables. The `createTablesForSchema` method handles the creation of all tables within a schema and their foreign key constraints.

#### Foreign Key Constraints

Foreign key constraints are added using `ALTER TABLE` statements after the tables are created.

#### Executing Queries

The `executeQuery` method executes the generated SQL queries using a PDO instance.

### Query Builders

Maestro provides query builder classes for common SQL operations: `Select`, `Insert`, `Update`, and `Delete`.

#### Select

The `Select` class allows you to build and execute SELECT queries.

```php
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Database\PDOConnection;

$select = new Select(PDOConnection::getInstance());
$select->from(['u' => 'users'], ['u.id', 'u.name'])
       ->where(['u.active' => true])
       ->order('u.name', 'ASC')
       ->limit(10);

$sql = $select->getSql();
$params = $select->getParameters();
```

#### Insert

The `Insert` class allows you to build and execute INSERT queries.

```php
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\PDOConnection;
use Maestro\Example\User;

$user = new User('John Doe', 'john@example.com', md5('password'), true, new Timestamp('now'));

$insert = new Insert(PDOConnection::getInstance());
$insert->into(User::class)
       ->values($user)
       ->returning(['id']);

$sql = $insert->getSql();
$params = $insert->getParameters();
```

#### Update

The `Update` class allows you to build and execute UPDATE queries.

```php
use Ilias\Maestro\Database\Update;
use Ilias\Maestro\Database\PDOConnection;

$update = new Update(PDOConnection::getInstance());
$update->table('users')
       ->set('name', 'Jane Doe')
       ->where(['id' => 1]);

$sql = $update->getSql();
$params = $update->getParameters();
```

#### Delete

The `Delete` class allows you to build and execute DELETE queries.

```php
use Ilias\Maestro\Database\Delete;
use Ilias\Maestro\Database\PDOConnection;

$delete = new Delete(PDOConnection::getInstance());
$delete->from('users')
       ->where(['id' => 1]);

$sql = $delete->getSql();
$params = $delete->getParameters();
```

### Examples

#### Defining a Schema and Tables

```php
<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Schema;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Interface\PostgresFunction;
use Ilias\Maestro\Types\Timestamp;

final class Hr extends Schema
{
    public User $user;
}

final class User extends Table
{
  public Hr $schema;
  public string $username;
  public string $email;
  public string $password;
  public Timestamp | PostgresFunction | string $createdIn = "CURRENT_TIMESTAMP";

  public function __construct(
    string $username,
    string $email,
    string $password,
    Timestamp $createdIn
  ) {
    $this->username = $username;
    $this->email = $email;
    $this->password = $password;
    $this->createdIn = $createdIn;
  }

  public static function getUniqueColumns(): array
  {
    return ["username", "email"];
  }
}
```
Explanations:
- `final`: Use the final directive declaring your `Table`, `Schema` and `Database` classes. This is the application's way of keeping track of the created entities.
- `type`: Declare all types of class attributes so that the application can better choose the equivalent data type from the database column.
- `__construct`: The construct method is used to define the non-nullability of a database column. Add to the constructor arguments the columns that must not be null.
- `default`: To declare the default value, simply add an initial value to the class attribute.
- `custom function`: To use a postgres function as the default value, follow the previous step and add the following two typings: `<current type> | PostgresFunction | string` to the attribute type, then the text added as a value will be used as a function.
- `unique`: To identify unique columns, override the static function `getUniqueColumns` and return the names of the columns that should be unique.

#### Generating SQL Queries

```php
<?php

require_once 'vendor/autoload.php';

use Ilias\Maestro\Database\DatabaseManager;
use Maestro\Example\Hr;
use Maestro\Example\User;
use PDO;

// Initialize PDO
$pdo = new PDO('pgsql:host=localhost;dbname=testdb', 'username', 'password');

// Initialize DatabaseManager
$dbManager = new DatabaseManager($pdo);

// Create schemas and tables based on the defined classes
$queries = $dbManager->createTablesForSchema(new Hr());

foreach ($queries as $query) {
    $dbManager->executeQuery($pdo, $query);
}
```
