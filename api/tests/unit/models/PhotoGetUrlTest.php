<?php

namespace api\tests\unit\models;

use Codeception\Test\Unit;
use common\models\Photo;

class PhotoGetUrlTest extends Unit
{
    public function testGetUrlReturnsAbsoluteUrl(): void
    {
        \Yii::$app->params['appBaseUrl'] = 'http://test.example';
        $photo = new Photo();
        $url = $photo->getUrl();

        $this->assertStringStartsWith('http', $url);
        $this->assertStringContainsString('/images/static/', $url);
    }

    public function testGetUrlFieldExists(): void
    {
        $photo = new Photo();
        $fields = $photo->fields();
        $this->assertArrayHasKey('url', $fields);
        $this->assertIsCallable($fields['url']);
    }
}
