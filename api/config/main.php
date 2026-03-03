<?php

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            'class' => \api\modules\v1\Module::class,
        ],
    ],
    'components' => [
        'request' => [
            'parsers' => [
                'application/json' => \yii\web\JsonParser::class,
            ],
        ],
        'response' => [
            'format' => \yii\web\Response::FORMAT_JSON,
        ],
        'user' => [
            'identityClass' => \common\models\User::class,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'class' => \api\components\JsonErrorHandler::class,
            'errorAction' => null,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'GET health' => 'site/health',
                'GET v1' => 'site/index',
                'GET users' => 'v1/user/index',
                'GET users/<id:\d+>' => 'v1/user/view',
                'GET albums' => 'v1/album/index',
                'GET albums/<id:\d+>' => 'v1/album/view',
                'GET v1/users' => 'v1/user/index',
                'GET v1/users/<id:\d+>' => 'v1/user/view',
                'GET v1/albums' => 'v1/album/index',
                'GET v1/albums/<id:\d+>' => 'v1/album/view',
            ],
        ],
    ],
    'params' => $params,
];
