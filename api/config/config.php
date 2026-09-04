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
	//  'db' => [
	// 	 'driver'   => 'pgsql',
	// 	 'host'     => 'localhost',
	// 	 'port'     => '5432',
	// 	 'database' => 'postgres',
	// 	 'schema'   => 'aghu_stg',
	// 	 'username' => 'postgres',
	// 	 'password' => '13676616766'
	//  ],
	
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