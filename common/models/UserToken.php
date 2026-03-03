<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * UserToken for Bearer auth. Stores token hash, not plaintext.
 *
 * @property int $id
 * @property int $user_id
 * @property string $token_hash
 * @property int $created_at
 * @property int|null $revoked_at
 *
 * @property User $user
 */
class UserToken extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user_token}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'token_hash', 'created_at'], 'required'],
            [['user_id', 'created_at', 'revoked_at'], 'integer'],
            [['token_hash'], 'string', 'max' => 64],
        ];
    }

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function findValidByToken(string $plainToken): ?self
    {
        $hash = hash('sha256', $plainToken);
        return static::find()
            ->where(['token_hash' => $hash])
            ->andWhere(['revoked_at' => null])
            ->one();
    }

    public static function createForUser(int $userId): array
    {
        $plainToken = \Yii::$app->security->generateRandomString(32);
        $model = new static();
        $model->user_id = $userId;
        $model->token_hash = hash('sha256', $plainToken);
        $model->created_at = time();
        $model->save(false);

        return [$model, $plainToken];
    }
}
