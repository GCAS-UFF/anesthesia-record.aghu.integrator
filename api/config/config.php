<?php

return [
    'db' => [
        'driver'   => 'pgsql',
        'host'     => getenv('DB_HOST'),
		'port'     => getenv('DB_PORT'),
		'database' => getenv('DB_DATABASE'),
		'schema'   => getenv('DB_SCHEMA'),
		'username' => getenv('DB_USERNAME'),
		'password' => getenv('DB_PASSWORD'),
    ],

    'api' => [
        'basePath' => '/public',
        'timezone' => 'America/Sao_Paulo',
        'debug'    => true
    ],

    'cors' => [
        'enabled' => true,
        'origin'  => '*'
    ]
];