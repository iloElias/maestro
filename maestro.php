<?php

require_once("./vendor/autoload.php");

// use Ilias\Maestro\Database\Delete;
// use Ilias\Maestro\Core\Maestro;
use Ilias\Maestro\Core\Manager;
// use Ilias\Maestro\Database\Insert;
// use Ilias\Maestro\Database\Connection;
// use Ilias\Maestro\Database\Select;
// use Ilias\Maestro\Types\Timestamp;
// use Ilias\Maestro\Utils\Utils;
use Maestro\Example\MaestroDb;
use Maestro\Example\Post;
use Maestro\Example\User;

$coreDatabase = new Manager();
$agrofastDB = new MaestroDb();
// new User("nickname", "email", "password", true, new Timestamp());

print implode("\n", $coreDatabase->createDatabase($agrofastDB, false)) . "\n";

// var_dump(
//   User::tableCreationInfo()
// );

// var_dump(
//   Post::tableCreationInfo()
// );

// $insert = new Insert(Maestro::SQL_NO_PREDICT, Connection::get());
// $user = new User("nickname'-- drop table", 'John', 'Doe', 'email@example.com', 'password', true, new Timestamp());
// $result = $insert->into($user)->values($user)->returning(['id'])->execute();
// var_dump($result);

// $delete = new Delete(Maestro::SQL_NO_PREDICT, Connection::get());
// $delete->from($user)->where(['id' => $result[0]['id']])->execute();


// $where = ["teste" => null];

// var_dump( implode(" teste ", $where), empty($where));
