<?php

return [
    'id' => 'app-api-tests',
    'components' => [
        'request' => [
            'cookieValidationKey' => 'test',
            'baseUrl' => '/index-test.php',
            'scriptUrl' => '/index-test.php',
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
    ],
];
