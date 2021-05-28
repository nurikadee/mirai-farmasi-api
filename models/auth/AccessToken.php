<?php

namespace app\models\auth;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class AccessToken extends ActiveRecord
{
    public $tokenExpiration = 60 * 24 * 365; // in seconds
    public $defaultAccessGiven = '{"access":["all"]}';
    public $defaultConsumer = 'mobile';

    public static function tableName()
    {
        return 'mirai_farmasi.tb_access_token';
    }

    public static function generateAuthKey($user)
    {
        // $this->auth_key = Yii::$app->security->generateRandomString();
        $accessToken = new AccessToken();
        $accessToken->user_id = $user->id;
        $accessToken->consumer = $user->consumer ?? $accessToken->defaultConsumer;
        $accessToken->access_given = $user->access_given ?? $accessToken->defaultAccessGiven;
        $accessToken->token = $user->auth_key;
        $accessToken->used_at = strtotime("now");
        $accessToken->expire_at = $accessToken->tokenExpiration + $accessToken->used_at;
        $accessToken->save();
    }

    public static function makeAllUserTokenExpiredByUserId($userId)
    {
        AccessToken::updateAll(['expire_at' => strtotime("now")], ['user_id' => $userId]);
    }

    public function expireThisToken()
    {
        $this->expire_at = strtotime("now");
        return $this->save();
    }

    public function rules()
    {
        return [
            [['user_id', 'used_at', 'expire_at'], 'default', 'value' => null],
            [['user_id', 'used_at', 'expire_at'], 'integer'],
            [['consumer', 'token', 'access_given'], 'string'],
            [['token', 'access_given'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'consumer' => 'Consumer',
            'token' => 'Token',
            'access_given' => 'Access Given',
            'used_at' => 'Used At',
            'expire_at' => 'Expire At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
