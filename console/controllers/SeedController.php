<?php

namespace console\controllers;

use common\models\Album;
use common\models\Photo;
use common\models\User;
use common\models\UserToken;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Connection;

class SeedController extends Controller
{
    private function getPassword(): string
    {
        $password = $_ENV['DEMO_USER_PASSWORD'] ?? getenv('DEMO_USER_PASSWORD');
        if (empty($password)) {
            throw new \RuntimeException(
                'DEMO_USER_PASSWORD must be set in .env (copy from .env.example and fill).'
            );
        }
        return $password;
    }

    public function actionUsers(): int
    {
        $password = $this->getPassword();
        $this->truncateTables(['user_token', 'photo', 'album', 'user']);

        $tokenToPrint = null;
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->first_name = "First$i";
            $user->last_name = "Last$i";
            $user->setPassword($password);
            $user->save(false);

            [$tokenModel, $plainToken] = UserToken::createForUser((int) $user->id);
            if ($i === 1) {
                $tokenToPrint = $plainToken;
            }
        }

        $this->stdout("Created 10 users.\n");
        if ($tokenToPrint) {
            $this->stdout("\n=== Use this token for API testing (user #1) ===\n");
            $this->stdout($tokenToPrint . "\n");
            $this->stdout("==============================================\n");
        }
        return ExitCode::OK;
    }

    public function actionAlbums(): int
    {
        $this->truncateTables(['photo', 'album']);
        $userIds = User::find()->select('id')->column();
        if (empty($userIds)) {
            $this->stderr("Run yii seed/users first.\n");
            return ExitCode::DATAERR;
        }

        $faker = \Faker\Factory::create();
        for ($i = 0; $i < 100; $i++) {
            $album = new Album();
            $album->user_id = $userIds[array_rand($userIds)];
            $album->title = $faker->sentence(4);
            $album->save(false);
        }

        $this->stdout("Created 100 albums.\n");
        return ExitCode::OK;
    }

    public function actionPhotos(): int
    {
        $this->truncateTables(['photo']);
        $albumIds = Album::find()->select('id')->column();
        if (empty($albumIds)) {
            $this->stderr("Run yii seed/albums first.\n");
            return ExitCode::DATAERR;
        }

        $faker = \Faker\Factory::create();
        for ($i = 0; $i < 1000; $i++) {
            $photo = new Photo();
            $photo->album_id = $albumIds[array_rand($albumIds)];
            $photo->title = $faker->sentence(3);
            $photo->save(false);
        }

        $this->stdout("Created 1000 photos.\n");
        return ExitCode::OK;
    }

    public function actionAll(): int
    {
        $this->actionUsers();
        $this->actionAlbums();
        $this->actionPhotos();
        $this->stdout("\nAll seed data created.\n");
        return ExitCode::OK;
    }

    private function truncateTables(array $tables): void
    {
        $db = \Yii::$app->db;
        assert($db instanceof Connection);
        $db->createCommand('SET FOREIGN_KEY_CHECKS=0')->execute();
        foreach ($tables as $table) {
            $db->createCommand()->truncateTable("{{%$table}}")->execute();
        }
        $db->createCommand('SET FOREIGN_KEY_CHECKS=1')->execute();
    }
}
