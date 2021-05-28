<?php

namespace app\helpers;

use Yii;
use yii\base\NotSupportedException;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

class AuthMethodsFromParamsHelper
{
    public static function authMethods()
    {
        $methods = new self();
        return $methods->getAuthMethods();
    }

    private function getAuthMethods()
    {
        $authMethodsArray = null;
        if (Yii::$app->params['useHttpBasicAuth']) $authMethodsArray[] = HttpBasicAuth::class;
        if (Yii::$app->params['useHttpBearerAuth']) $authMethodsArray[] = HttpBearerAuth::class;
        if (Yii::$app->params['useQueryParamAuth']) $authMethodsArray[] = QueryParamAuth::class;
        if ($authMethodsArray == null) throw new NotSupportedException('You must choose at least one auth methods, configure your app\config\params.php for more options.');
        return $authMethodsArray;
    }
}
