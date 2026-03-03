<?php

declare(strict_types=1);

namespace api\components;

use yii\web\ErrorHandler as BaseErrorHandler;
use yii\web\Response;

/**
 * JSON error response for API.
 */
class JsonErrorHandler extends BaseErrorHandler
{
    protected function renderException($exception): void
    {
        if (\Yii::$app->has('response')) {
            $response = \Yii::$app->getResponse();
        } else {
            $response = new Response();
        }

        $response->format = Response::FORMAT_JSON;
        $response->setStatusCode(
            $exception instanceof \yii\web\HttpException
                ? $exception->statusCode
                : 500
        );

        $response->data = [
            'name' => $exception->getName(),
            'message' => $exception->getMessage(),
        ];
        $response->send();
    }
}
