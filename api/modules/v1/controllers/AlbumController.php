<?php

declare(strict_types=1);

namespace api\modules\v1\controllers;

use api\modules\v1\assemblers\AlbumResourceAssembler;
use api\modules\v1\services\AlbumReadService;
use common\models\Album;
use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\IndexAction;
use yii\rest\ViewAction;
use yii\web\NotFoundHttpException;

class AlbumController extends BaseApiController
{
    public $modelClass = Album::class;

    private AlbumReadService $albumReadService;
    private AlbumResourceAssembler $albumResourceAssembler;

    public function init(): void
    {
        parent::init();
        $this->albumReadService = new AlbumReadService();
        $this->albumResourceAssembler = new AlbumResourceAssembler();
    }

    public function actions(): array
    {
        return [
            'index' => [
                'class' => IndexAction::class,
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'prepareDataProvider' => [$this, 'prepareAlbumDataProvider'],
            ],
            'view' => [
                'class' => ViewAction::class,
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'findModel' => [$this, 'findAlbumModel'],
            ],
        ];
    }

    public function prepareAlbumDataProvider(): ActiveDataProvider
    {
        $pageSize = (int) (Yii::$app->request->get('per-page', 20));
        $pageSize = $pageSize > 0 ? min($pageSize, 100) : 20;

        return new ActiveDataProvider([
            'query' => $this->albumReadService->getListQuery(),
            'pagination' => [
                'pageSize' => $pageSize,
                'pageSizeParam' => 'per-page',
            ],
        ]);
    }

    public function findAlbumModel($id): Album
    {
        $model = $this->albumReadService->findById((int) $id);
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Album not found.');
    }

    public function serializeData($data): array
    {
        if ($data instanceof Album && $data->isRelationPopulated('user') && $data->isRelationPopulated('photos')) {
            return $this->albumResourceAssembler->toDetail($data);
        }

        return parent::serializeData($data);
    }
}
