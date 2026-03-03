<?php

declare(strict_types=1);

namespace api\tests\_support\Helper;

use Codeception\Module;
use Yii;
use yii\web\Response;

/**
 * Helper for API tests. Provides JSON response access.
 */
class ApiHelper extends Module
{
    /**
     * Returns the last response as decoded JSON array.
     */
    public function grabJsonResponse(): array
    {
        $response = Yii::$app->response;
        if ($response->format === Response::FORMAT_JSON && is_array($response->data)) {
            return $response->data;
        }
        $content = $response->content ?? (is_string($response->data) ? $response->data : json_encode($response->data));
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($content) ? $content : [];
    }
}
