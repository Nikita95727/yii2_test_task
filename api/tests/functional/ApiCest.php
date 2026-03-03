<?php

declare(strict_types=1);

namespace api\tests\functional;

use common\models\User;
use common\models\UserToken;

/**
 * API functional tests.
 */
class ApiCest
{
    private const BASE_URL = 'http://api.test/index-test.php';

    private ?string $testToken = null;

    public function _before(\api\tests\FunctionalTester $I): void
    {
        $user = User::findOne(1);
        if ($user) {
            [, $plain] = UserToken::createForUser($user->id);
            $this->testToken = $plain;
        }
    }

    public function test401WithoutToken(\api\tests\FunctionalTester $I): void
    {
        $I->amOnPage(self::BASE_URL . '/users');
        $I->seeResponseCodeIs(401);
        $json = $I->grabJsonResponse();
        $I->assertArrayHasKey('name', $json);
        $I->assertArrayHasKey('message', $json);
        $I->assertStringContainsString('Unauthorized', $json['name'] ?? '');
    }

    public function test401WithInvalidToken(\api\tests\FunctionalTester $I): void
    {
        $I->haveHttpHeader('Authorization', 'Bearer invalid-token-12345');
        $I->amOnPage(self::BASE_URL . '/users');
        $I->seeResponseCodeIs(401);
        $json = $I->grabJsonResponse();
        $I->assertArrayHasKey('name', $json);
    }

    public function test200UsersWithToken(\api\tests\FunctionalTester $I): void
    {
        if (!$this->testToken) {
            $I->comment('Skipped: run yii migrate, yii seed/all');
            return;
        }
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->testToken);
        $I->amOnPage(self::BASE_URL . '/users');
        $I->seeResponseCodeIs(200);
        $json = $I->grabJsonResponse();
        $I->assertArrayHasKey('items', $json);
        $I->assertArrayHasKey('_meta', $json);
        $I->assertIsArray($json['items']);
        $I->assertArrayHasKey('totalCount', $json['_meta']);
        $I->assertArrayHasKey('perPage', $json['_meta']);
        if (count($json['items']) > 0) {
            $first = $json['items'][0];
            $I->assertArrayHasKey('id', $first);
            $I->assertArrayHasKey('first_name', $first);
            $I->assertArrayHasKey('last_name', $first);
        }
    }

    public function test200UsersIdWithAlbums(\api\tests\FunctionalTester $I): void
    {
        if (!$this->testToken) {
            return;
        }
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->testToken);
        $I->amOnPage(self::BASE_URL . '/users/1');
        $I->seeResponseCodeIs(200);
        $json = $I->grabJsonResponse();
        $I->assertArrayHasKey('id', $json);
        $I->assertArrayHasKey('first_name', $json);
        $I->assertArrayHasKey('last_name', $json);
        $I->assertArrayHasKey('albums', $json);
        $I->assertIsArray($json['albums']);
    }

    public function test200AlbumsListWithToken(\api\tests\FunctionalTester $I): void
    {
        if (!$this->testToken) {
            return;
        }
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->testToken);
        $I->amOnPage(self::BASE_URL . '/albums');
        $I->seeResponseCodeIs(200);
        $json = $I->grabJsonResponse();
        $I->assertArrayHasKey('items', $json);
        $I->assertArrayHasKey('_meta', $json);
        if (count($json['items']) > 0) {
            $first = $json['items'][0];
            $I->assertArrayHasKey('id', $first);
            $I->assertArrayHasKey('title', $first);
        }
    }

    public function test200AlbumsIdWithFirstNameLastNamePhotos(\api\tests\FunctionalTester $I): void
    {
        if (!$this->testToken) {
            return;
        }
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->testToken);
        $I->amOnPage(self::BASE_URL . '/albums/1');
        $I->seeResponseCodeIs(200);
        $json = $I->grabJsonResponse();
        $I->assertArrayHasKey('id', $json);
        $I->assertArrayHasKey('first_name', $json);
        $I->assertArrayHasKey('last_name', $json);
        $I->assertArrayHasKey('photos', $json);
        $I->assertIsArray($json['photos']);
        if (count($json['photos']) > 0) {
            $firstPhoto = $json['photos'][0];
            $I->assertArrayHasKey('id', $firstPhoto);
            $I->assertArrayHasKey('title', $firstPhoto);
            $I->assertArrayHasKey('url', $firstPhoto);
            $I->assertStringStartsWith('http', (string) $firstPhoto['url']);
        }
    }

    public function testPaginationPerPage(\api\tests\FunctionalTester $I): void
    {
        if (!$this->testToken) {
            return;
        }
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->testToken);
        $I->amOnPage(self::BASE_URL . '/users?per-page=3');
        $I->seeResponseCodeIs(200);
        $json = $I->grabJsonResponse();
        $I->assertArrayHasKey('items', $json);
        $I->assertArrayHasKey('_meta', $json);
        $I->assertSame(3, $json['_meta']['perPage'] ?? 0);
        $I->assertLessThanOrEqual(3, count($json['items']));
    }

    public function test404NotFound(\api\tests\FunctionalTester $I): void
    {
        if (!$this->testToken) {
            return;
        }
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->testToken);
        $I->amOnPage(self::BASE_URL . '/users/999999');
        $I->seeResponseCodeIs(404);
        $json = $I->grabJsonResponse();
        $I->assertArrayHasKey('name', $json);
        $I->assertArrayHasKey('message', $json);
    }

    public function testHealthPublic(\api\tests\FunctionalTester $I): void
    {
        $I->amOnPage(self::BASE_URL . '/health');
        $I->seeResponseCodeIs(200);
        $json = $I->grabJsonResponse();
        $I->assertArrayHasKey('status', $json);
        $I->assertSame('ok', $json['status'] ?? '');
    }

    public function testV1RoutesWork(\api\tests\FunctionalTester $I): void
    {
        if (!$this->testToken) {
            return;
        }
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->testToken);
        $I->amOnPage(self::BASE_URL . '/v1/users/1');
        $I->seeResponseCodeIs(200);
        $json = $I->grabJsonResponse();
        $I->assertArrayHasKey('albums', $json);
    }
}
