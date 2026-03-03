<?php

declare(strict_types=1);

namespace api\modules\v1\assemblers;

use common\models\User;

/**
 * Maps User models to API response structures.
 */
class UserResourceAssembler
{
    /**
     * Assembles user detail with albums for GET /users/{id}.
     */
    public function toDetail(User $user): array
    {
        $albums = [];
        if ($user->isRelationPopulated('albums')) {
            foreach ($user->albums as $album) {
                $albums[] = [
                    'id' => $album->id,
                    'title' => $album->title,
                ];
            }
        }

        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'albums' => $albums,
        ];
    }
}
