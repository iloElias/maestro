# Maestro Documentation

[![Maintainer](http://img.shields.io/badge/maintainer-@iloElias-blue.svg)](https://github.com/iloElias)
[![Package](https://img.shields.io/badge/package-iloelias/maestro-orange.svg)](https://packagist.org/packages/ilias/maestro)
[![Source Code](https://img.shields.io/badge/source-iloelias/maestro-blue.svg)](https://github.com/iloElias/maestro)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)

Maestro is a powerful, flexible PHP library for building and managing SQL queries programmatically. It allows developers to construct complex queries using a fluent and object-oriented interface, without writing raw SQL. This enables safer, more maintainable, and readable code when interacting with databases.

## Table of Contents

- [Installation](#installation)
- [Query Builders](#query-builders)
  - [Select](#select)

## Installation

To install Maestro, use Composer:

```sh
composer require ilias/maestro
```

## Query Builders

### Select

```php
use Ilias\Maestro\Abstract\Query\Select;

$select = (new Select(['id', 'name']))
    ->from('users')
    ->where(['status' => 'active'])
    ->group(['role'])
    ->having('COUNT(id) > 1');
```
