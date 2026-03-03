<?php

declare(strict_types=1);

namespace api\controllers;

use yii\rest\Controller;
use yii\web\Response;

/**
 * Public endpoints (no auth).
 */
class SiteController extends Controller
{
    public function actionHealth(): array
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        return ['status' => 'ok', 'message' => 'API available'];
    }

    public function actionIndex(): array
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        return ['status' => 'ok', 'message' => 'API v1 available'];
    }
}
