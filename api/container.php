<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Level;

return function () {
    $containerBuilder = new \DI\ContainerBuilder();

    $containerBuilder->addDefinitions([
        // Monolog Logger
        Logger::class => function () {
            $logger = new Logger('wart-stat');
            
            // Create logs directory if it doesn't exist
            $logsDir = __DIR__ . '/../logs';
            if (!is_dir($logsDir)) {
                if (!mkdir($logsDir, 0755, true) && !is_dir($logsDir)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $logsDir));
                }
            }
            $handler = new StreamHandler($logsDir . '/app.log', Level::Debug);
            $handler->setFormatter(new \Monolog\Formatter\JsonFormatter());
            $logger->pushHandler($handler);
            return $logger;
        },

        // PDO SQLite connection
        \PDO::class => function () {
            $dbPath = __DIR__ . '/../data/database.sqlite';

            // Create data directory if it doesn't exist
            $dataDir = dirname($dbPath);
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0755, true);
            }

            $pdo = new \PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

            return $pdo;
        },
    ]);

    // Here you can add definitions to the container
    // $containerBuilder->addDefinitions([...]);

    return $containerBuilder->build();
};