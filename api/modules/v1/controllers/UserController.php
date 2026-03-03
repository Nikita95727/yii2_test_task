<?php

declare(strict_types=1);

namespace api\modules\v1\controllers;

use api\modules\v1\assemblers\UserResourceAssembler;
use api\modules\v1\services\UserReadService;
use common\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\IndexAction;
use yii\rest\ViewAction;
use yii\web\NotFoundHttpException;

class UserController extends BaseApiController
{
    public $modelClass = User::class;

    private UserReadService $userReadService;
    private UserResourceAssembler $userResourceAssembler;

    public function init(): void
    {
        parent::init();
        $this->userReadService = new UserReadService();
        $this->userResourceAssembler = new UserResourceAssembler();
    }

    public function actions(): array
    {
        return [
            'index' => [
                'class' => IndexAction::class,
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'prepareDataProvider' => [$this, 'prepareUserDataProvider'],
            ],
            'view' => [
                'class' => ViewAction::class,
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'findModel' => [$this, 'findUserModel'],
            ],
        ];
    }

    public function prepareUserDataProvider(): ActiveDataProvider
    {
        $pageSize = (int) (Yii::$app->request->get('per-page', 20));
        $pageSize = $pageSize > 0 ? min($pageSize, 100) : 20;

        return new ActiveDataProvider([
            'query' => $this->userReadService->getListQuery(),
            'pagination' => [
                'pageSize' => $pageSize,
                'pageSizeParam' => 'per-page',
            ],
        ]);
    }

    public function findUserModel($id): User
    {
        $model = $this->userReadService->findById((int) $id);
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('User not found.');
    }

    public function serializeData($data): array
    {
        if ($data instanceof User && $data->isRelationPopulated('albums')) {
            return $this->userResourceAssembler->toDetail($data);
        }

        return parent::serializeData($data);
    }
}
