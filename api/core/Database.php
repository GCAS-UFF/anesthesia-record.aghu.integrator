<?php

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {

            $config = require dirname(__DIR__) . '/config/config.php';

            $db = $config['db'];

            self::$connection = new PDO(
                "{$db['driver']}:host={$db['host']};port={$db['port']};dbname={$db['database']}",
                $db['username'],
                $db['password']
            );

            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }

        return self::$connection;
    }
}