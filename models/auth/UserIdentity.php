<?php

namespace app\models\auth;

use app\models\User;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use Yii;

class UserIdentity extends ActiveRecord implements IdentityInterface
{
    public $consumer;

    public static function tableName()
    {
        return '{{%mirai_farmasi.tb_user}}';
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => User::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        $accessToken = AccessToken::find()->where(['token' => $token])->andWhere(['>', 'expire_at', strtotime('now')])->one();
        if (!$accessToken) return $accessToken;
        return User::findOne(['id' => $accessToken->user_id]);
        // return User::findOne(['auth_key' => $token, 'status' => User::STATUS_ACTIVE]);
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
        AccessToken::generateAuthKey($this);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }
}
