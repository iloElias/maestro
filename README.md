# Maestro Documentation

[![Maintainer](http://img.shields.io/badge/maintainer-@iloElias-blue.svg)](https://github.com/iloElias)
[![Maintainer](http://img.shields.io/badge/maintainer-@dhenriquearantes-blue.svg)](https://github.com/dhenriquearantes)
[![Package](https://img.shields.io/badge/package-iloelias/maestro-orange.svg)](https://packagist.org/packages/ilias/maestro)
[![Source Code](https://img.shields.io/badge/source-iloelias/maestro-blue.svg)](https://github.com/iloElias/maestro)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)

Maestro is a PHP library designed to facilitate the creation and management of PostgreSQL database schemas and tables. It allows developers to define schemas and tables using PHP classes, providing a clear and structured way to manage database definitions and relationships.

## Table of Contents

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

## Installation

To install Maestro, use Composer:

```sh
composer require ilias/maestro
```

## Schema and Table Classes

Maestro uses abstract classes for schemas and tables. Developers extend these classes to define their own schemas and tables.

## Defining Schemas and Tables

### Schema Class

A schema class extends the `Schema` abstract class. It contains table attributes that are typed with the table classes.

### Table Class

A table class extends the `Table` abstract class. It can define columns as class properties, specifying their types and optional default values.

### Unique Columns

You can specify columns that should be unique by overriding the `tableUniqueColumns` method in your table class.
You can also define a column as unique by adding the `@unique` clause to the documentation of the attribute you want to make unique. This format will not work if the `tableUniqueColumns` method is overridden.

### Default Values

Columns can have default values. If a default value is a PostgreSQL function, it should be defined as a `PostgresFunction` type to ensure it is not quoted in the final SQL query.

## DatabaseManager

The `DatabaseManager` class provides methods to create schemas, tables, and manage foreign key constraints.

### Creating Schemas and Tables

The `createSchema` and `createTable` methods generate SQL queries to create schemas and tables. The `createTablesForSchema` method handles the creation of all tables within a schema and their foreign key constraints.

### Foreign Key Constraints

Foreign key constraints are added using `ALTER TABLE` statements after the tables are created.

### Executing Queries

The `executeQuery` method executes the generated SQL queries using a PDO instance.

## Query Builders

Maestro provides query builder classes for common SQL operations: `Select`, `Insert`, `Update`, and `Delete`.

### Select

The `Select` class allows you to build and execute SELECT queries.

```php
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Database\Connection;

$select = new Select(Connection::get());
$select->from(['u' => 'users'], ['u.id', 'u.name'])
       ->where(['u.active' => true])
       ->order('u.name', 'ASC')
       ->limit(10);

$sql = $select->getSql();
$params = $select->getParameters();
```

### Insert

The `Insert` class allows you to build and execute INSERT queries.

```php
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\Connection;
use Maestro\Example\User;

$user = new User('John Doe', 'john@example.com', md5('password'), true, new Timestamp('now'));

$insert = new Insert(Connection::get());
$insert->into(User::class)
       ->values($user)
       ->returning(['id']);

$sql = $insert->getSql();
$params = $insert->getParameters();
```

### Update

The `Update` class allows you to build and execute UPDATE queries.

```php
use Ilias\Maestro\Database\Update;
use Ilias\Maestro\Database\Connection;

$update = new Update(Connection::get());
$update->table('users')
       ->set('name', 'Jane Doe')
       ->where(['id' => 1]);

$sql = $update->getSql();
$params = $update->getParameters();
```

### Delete

The `Delete` class allows you to build and execute DELETE queries.

```php
use Ilias\Maestro\Database\Delete;
use Ilias\Maestro\Database\Connection;

$delete = new Delete(Connection::get());
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
use Ilias\Maestro\Abstract\PostgresFunction;
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

  public function compose(
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

  public static function tableUniqueColumns(): array
  {
    return ["username", "email"];
  }
}
```

Explanations:

- `final`: Use the final directive declaring your `Table`, `Schema` and `Database` classes. This is the application's way of keeping track of the created entities.
- `type`: Declare all types of class attributes so that the application can better choose the equivalent data type from the database column.
- `compose`: The compose method is used to define the non-nullability of a database column. Add to the constructor arguments the columns that must not be null.
- `default`: To declare the default value, simply add an initial value to the class attribute.
- `custom function`: To use a postgres function as the default value, follow the previous step and add the following two typings: `<current type> | PostgresFunction | string` to the attribute type, then the text added as a value will be used as a function.
- `unique`: Override the static `tableUniqueColumns` method to return the names of unique columns. Alternatively, use the `@unique` clause in the attribute's documentation, but note this won't work if `tableUniqueColumns` is overridden.

#### Generating SQL Queries

Your file should typically include the following variables:

1. **DB_SQL**: The PHP data source driver name.
2. **DB_HOST**: The hostname of your database server.
3. **DB_PORT**: The port number on which your database server is running.
4. **DB_NAME**: The name of the database you want to connect to.
5. **DB_USER**: The username used to connect to the database.
6. **DB_PASS**: The password used to connect to the database.

Here is what your .env file need to have:

```plaintext
DB_SQL=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_NAME=maestrodb
DB_USER=postgres
DB_PASS=dbpass
```

```php
<?php

require_once 'vendor/autoload.php';

use Ilias\Maestro\Database\DatabaseManager;
use Ilias\Maestro\Database\Connection;
use Maestro\Example\Hr;
use PDO;

// Initialize PDO with environment variables
$pdo = Connection::get();

// Initialize DatabaseManager
$dbManager = new DatabaseManager($pdo);

// Create schemas and tables based on the defined classes
$queries = $dbManager->createTablesForSchema(new Hr());

foreach ($queries as $query) {
    $dbManager->executeQuery($pdo, $query);
}
```

### Commands

Maestro provides several commands to help you manage and synchronize your database schema. Here are the available commands:

#### `sync-schema`

Synchronizes the schema for a specific schema class. This command ensures that the database schema for the specified class matches the schema defined in your PHP code.

##### Usage

```bash
./vendor/bin/maestro sync-schema <SchemaClass>
```

##### Example

```bash
./vendor/bin/maestro sync-schema Maestro\\Example\\Hr
```

This command will synchronize the `Hr` schema, ensuring that the database tables and columns for the `Hr` schema match the definitions in your PHP code.

#### `sync-database`

Synchronizes the entire database schema for a specified database class. This command iterates through all the schemas defined in the database class and ensures that each schema in the database matches the schema defined in your PHP code.

##### Usage

```bash
./vendor/bin/maestro sync-database <DatabaseClass>
```

##### Example

```bash
./vendor/bin/maestro sync-database Maestro\\Example\\MaestroDb
```

This command will synchronize all the schemas defined in the `MaestroDb` class, ensuring that the database tables and columns for each schema match the definitions in your PHP code.
