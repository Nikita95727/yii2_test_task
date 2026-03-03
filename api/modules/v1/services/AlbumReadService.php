<?php

declare(strict_types=1);

namespace api\modules\v1\services;

use common\models\Album;
use yii\db\ActiveQuery;

/**
 * Read-only service for Album domain. Handles queries and eager loading.
 */
class AlbumReadService
{
    public function getListQuery(): ActiveQuery
    {
        return Album::find()
            ->select(['id', 'title'])
            ->orderBy(['id' => SORT_ASC]);
    }

    public function findById(int $id): ?Album
    {
        return Album::find()
            ->select(['id', 'user_id', 'title'])
            ->with([
                'user' => fn (ActiveQuery $q) => $q->select(['id', 'first_name', 'last_name']),
                'photos' => fn (ActiveQuery $q) => $q->select(['id', 'album_id', 'title'])->orderBy(['id' => SORT_ASC]),
            ])
            ->where(['id' => $id])
            ->one();
    }
}
