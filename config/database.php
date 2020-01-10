<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'jyzj' => [
            'driver' => 'mysql',
            'host' => '134.175.142.205',
            'port' => '3306',
            'database' => 'jh_s20003',
            'username' => 'root',
            'password' => 'angel198297',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'jyzj_chat' => [
            'driver' => 'mysql',
            'host' => '134.175.116.186',
            'port' => '3306',
            'database' => 'jh_l20003',
            'username' => 'root',
            'password' => 'angel198297',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'bmsg' => [
            'driver' => 'mysql',
            'host' => '134.175.142.205',
            'port' => '3306',
            'database' => 'jh_s20004',
            'username' => 'root',
            'password' => 'angel198297',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'bmsg_chat' => [
            'driver' => 'mysql',
            'host' => '134.175.116.186',
            'port' => '3306',
            'database' => 'jh_l20004',
            'username' => 'root',
            'password' => 'angel198297',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'bhgr' => [
            'driver' => 'mysql',
            'host' => '134.175.142.205',
            'port' => '3306',
            'database' => 'jh_s20005',
            'username' => 'root',
            'password' => 'angel198297',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'bhgr_chat' => [
            'driver' => 'mysql',
            'host' => '134.175.116.186',
            'port' => '3306',
            'database' => 'jh_l20005',
            'username' => 'root',
            'password' => 'angel198297',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'twzx' => [
            'driver' => 'mysql',
            'host' => '134.175.142.205',
            'port' => '3306',
            'database' => 'jh_s20006',
            'username' => 'root',
            'password' => 'angel198297',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'twzx_chat' => [
            'driver' => 'mysql',
            'host' => '134.175.116.186',
            'port' => '3306',
            'database' => 'jh_l20006',
            'username' => 'root',
            'password' => 'angel198297',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'wxfyl' => [
            'driver' => 'mysql',
            'host' => '134.175.142.205',
            'port' => '3306',
            'database' => 'jh_s20002',
            'username' => 'root',
            'password' => 'angel198297',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'qimen' => [
            'driver' => 'mysql',
            'host' => '134.175.142.205',
            'port' => '3306',
            'database' => 'jh_s20003',
            'username' => 'root',
            'password' => 'angel198297',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'order' => [
            'driver' => 'mysql',
            'host' => '193.112.16.59',
            'port' => '3306',
            'database' => 'jh_login3_logs',
            'username' => 'root',
            'password' => 'angel198297',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'account' => [
            'driver' => 'mysql',
            'host' => '193.112.16.59',
            'port' => '3306',
            'database' => 'jh_login3',
            'username' => 'root',
            'password' => 'angel198297',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'wxfyl_s2002' => [
            'driver' => 'mysql',
            'host' => '118.89.27.234',
            'port' => '3306',
            'database' => 'jh_s2002',
            'username' => 'root',
            'password' => 'angel198297',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'qimen_s20003' => [
            'driver' => 'mysql',
            'host' => '134.175.116.186',
            'port' => '3306',
            'database' => 'jh_l20003',
            'username' => 'root',
            'password' => 'angel198297',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'wxfyl_s2006' => [
            'driver' => 'mysql',
            'host' => '118.89.27.234',
            'port' => '3306',
            'database' => 'jh_s2006',
            'username' => 'root',
            'password' => 'angel198297',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'wxfyl_l2002' => [
            'driver' => 'mysql',
            'host' => '134.175.116.186',
            'port' => '3306',
            'database' => 'jh_l20002',
            'username' => 'root',
            'password' => 'angel198297',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'predis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'predis'),
            'prefix' => Str::slug(env('APP_NAME', 'laravel'), '_').'_database_',
        ],

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ],

        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
        ],

    ],

];
