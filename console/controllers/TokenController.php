<?php

declare(strict_types=1);

namespace console\controllers;

use common\models\User;
use common\models\UserToken;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Generates Bearer tokens for API authentication.
 *
 * Usage:
 *   yii token/generate <id>
 *
 * Example:
 *   yii token/generate 1
 */
class TokenController extends Controller
{
    /**
     * Generate a Bearer token for a user.
     *
     * @param int $id User ID
     * @return int Exit code
     */
    public function actionGenerate(int $id): int
    {
        $user = User::findOne($id);
        if ($user === null) {
            $this->stderr("User with id {$id} not found.\n");

            return ExitCode::DATAERR;
        }

        [, $plainToken] = UserToken::createForUser((int) $user->id);

        $this->stdout("Bearer token for user #{$id} ({$user->first_name} {$user->last_name}):\n");
        $this->stdout($plainToken . "\n");

        return ExitCode::OK;
    }
}
