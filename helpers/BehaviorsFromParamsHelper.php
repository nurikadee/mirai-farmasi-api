<?php

namespace app\helpers;

use Yii;
use yii\filters\auth\CompositeAuth;

class BehaviorsFromParamsHelper
{
    public static function behaviors($behaviors)
    {
        $behaviorsFromParamsHelperObject = new self();
        return $behaviorsFromParamsHelperObject->getBehaviors($behaviors);
    }

    private function getBehaviors($behaviors)
    {
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => AuthMethodsFromParamsHelper::authMethods(),
        ];
        if (Yii::$app->params['useRateLimiter']) {
            $behaviors['rateLimiter']['enableRateLimitHeaders'] = false;
        }
        return $behaviors;
    }
}
