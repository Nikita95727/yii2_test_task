<?php

declare(strict_types=1);

namespace api\modules\v1\services;

use common\models\User;
use yii\db\ActiveQuery;

/**
 * Read-only service for User domain. Handles queries and eager loading.
 */
class UserReadService
{
    public function getListQuery(): ActiveQuery
    {
        return User::find()
            ->select(['id', 'first_name', 'last_name'])
            ->orderBy(['id' => SORT_ASC]);
    }

    public function findById(int $id): ?User
    {
        return User::find()
            ->select(['id', 'first_name', 'last_name'])
            ->with(['albums' => fn (ActiveQuery $q) => $q->select(['id', 'user_id', 'title'])
            ->orderBy(['id' => SORT_ASC])])
            ->where(['id' => $id])
            ->one();
    }
}
