<?php

use Ilias\Helper\App;
use Ilias\Maestro\Abstract\Query\Select;

require_once __DIR__ . '/../vendor/autoload.php';

App::configure(__DIR__ . '/../');

// $const = config('database.connections');

// echo var_dump($const);

$select = (new Select(['name', 'price']))
    ->from('product')
    ->where('')
;
