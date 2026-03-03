<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Photo model. URL is virtual - not stored in DB.
 *
 * @property int $id
 * @property int $album_id
 * @property string $title
 *
 * @property Album $album
 */
class Photo extends ActiveRecord
{
    private static ?array $staticImageFiles = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%photo}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['album_id', 'title'], 'required'],
            [['album_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function getAlbum(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Album::class, ['id' => 'album_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function fields(): array
    {
        return [
            'id',
            'title',
            'url' => function () {
                return $this->getUrl();
            },
        ];
    }

    /**
     * Virtual attribute: returns absolute URL to a random static image.
     */
    public function getUrl(): string
    {
        $files = self::getStaticImageFiles();
        $file = $files[array_rand($files)];
        $baseUrl = \Yii::$app->params['appBaseUrl'] ?? null;
        if ($baseUrl) {
            return rtrim($baseUrl, '/') . $file;
        }
        return Url::to($file, true);
    }

    private static function getStaticImageFiles(): array
    {
        if (self::$staticImageFiles === null) {
            $dir = \Yii::getAlias('@api/web/images/static');
            $files = [];
            if (is_dir($dir)) {
                foreach (glob($dir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: [] as $path) {
                    $files[] = '/images/static/' . basename($path);
                }
            }
            self::$staticImageFiles = $files ?: ['/images/static/placeholder.png'];
        }
        return self::$staticImageFiles;
    }
}
