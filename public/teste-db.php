<?php

$config = require '../api/config/config.php';

try {
    $dsn = sprintf(
        "pgsql:host=%s;port=%d;dbname=%s",
        $config['db']['host'],
        $config['db']['port'],
        $config['db']['database']
    );

    $pdo = new PDO(
        $dsn,
        $config['db']['username'],
        $config['db']['password']
    );

    echo "Conectado com sucesso!";
} catch (Throwable $e) {
    echo $e->getMessage();
}