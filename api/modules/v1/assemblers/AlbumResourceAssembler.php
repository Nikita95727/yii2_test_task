<?php

declare(strict_types=1);

namespace api\modules\v1\assemblers;

use common\models\Album;

/**
 * Maps Album models to API response structures.
 */
class AlbumResourceAssembler
{
    /**
     * Assembles album detail with owner and photos for GET /albums/{id}.
     */
    public function toDetail(Album $album): array
    {
        $photos = [];
        if ($album->isRelationPopulated('photos')) {
            foreach ($album->photos as $photo) {
                $photos[] = [
                    'id' => $photo->id,
                    'title' => $photo->title,
                    'url' => $photo->getUrl(),
                ];
            }
        }

        $firstName = '';
        $lastName = '';
        if ($album->isRelationPopulated('user') && $album->user !== null) {
            $firstName = $album->user->first_name;
            $lastName = $album->user->last_name;
        }

        return [
            'id' => $album->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'photos' => $photos,
        ];
    }
}
