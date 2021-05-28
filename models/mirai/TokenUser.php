<?php

namespace app\models\mirai;

class TokenUser extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'mirai_farmasi.tb_token_user';
    }

    public static function findTokenByDeviceId($device_id)
    {
        return TokenUser::find()
            ->where(['device_id' => $device_id])
            ->one();
    }
}
