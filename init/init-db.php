<?php
use Illuminate\Support\Str;
use Illuminate\Database\Capsule\Manager as Capsule;

function transformDbconfigToCapsuleConfig($dbconfig)
{
    return [
        'driver' => 'mysql',
        'host' => $dbconfig['host'],
        'port' => $dbconfig['port'],
        'database' => $dbconfig['dbname'],
        'username' => $dbconfig['username'],
        'password' => $dbconfig['password'],
        'charset' => $dbconfig['charset'],
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ];
}

$dbconfig = json_decode(file_get_contents(__DIR__ . '/dbconfig.json'), true);

$capsule = new Capsule;
$capsule->addConnection(transformDbconfigToCapsuleConfig($dbconfig['production']), 'default');

//Make this Capsule instance available globally.
$capsule->setAsGlobal();

// Setup the Eloquent ORM.
$capsule->bootEloquent();
