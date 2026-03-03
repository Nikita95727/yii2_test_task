<?php

declare(strict_types=1);

namespace api\modules\v1\controllers;

use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use yii\rest\Serializer;

/**
 * Base controller for authenticated API endpoints.
 */
abstract class BaseApiController extends ActiveController
{
    public $serializer = [
        'class' => Serializer::class,
        'collectionEnvelope' => 'items',
    ];

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];

        return $behaviors;
    }

    public function checkAccess($action, $model = null, $params = []): void
    {
    }
}
