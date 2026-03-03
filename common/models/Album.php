<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * Album model
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 *
 * @property User $user
 * @property Photo[] $photos
 */
class Album extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%album}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'title'], 'required'],
            [['user_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhotos()
    {
        return $this->hasMany(Photo::class, ['album_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function fields(): array
    {
        return ['id', 'title'];
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields(): array
    {
        return ['photos'];
    }
}
