<?php

namespace app\models\sso;

class AknUser extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'sso.akn_user';
    }
}
