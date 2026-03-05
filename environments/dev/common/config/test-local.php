<?php

return [
    'components' => [
        'db' => [
            'dsn' => $_ENV['TEST_DB_DSN'] ?? str_replace(
                'dbname=yii2api',
                'dbname=yii2api_test',
                $_ENV['DB_DSN'] ?? 'mysql:host=127.0.0.1;port=3306;dbname=yii2api'
            ),
        ],
    ],
];
