<?php

use Ilias\Helper\App;

require_once __DIR__ . '/../vendor/autoload.php';

App::configure(__DIR__ . '/../');

$a = App::rootDir();

echo var_dump($a);

// Interceptor::boot();

// $const = config('database.connections.pgsql');

// echo var_dump($const);
