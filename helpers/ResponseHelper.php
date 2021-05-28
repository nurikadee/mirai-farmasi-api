<?php

namespace app\helpers;

use Yii;

class ResponseHelper
{
    public static function success($status, $msg, $data)
    {
        Yii::$app->response->statusCode = $status;

        $response['isSuccess'] = 201;
        $response['message'] = $msg;

        return [
            'status' => $status,
            'message' => $msg,
            'data' => $data,
        ];
    }

    public static function errorSaving($model, $status, $msg)
    {
        Yii::$app->response->statusCode = $status;

        $model->getErrors();

        $response['hasErrors'] = $model->hasErrors();
        $response['errors'] = $model->getErrors();

        return [
            'status' => $status,
            'message' => $msg,
            'data' => [
                'hasErrors' => $model->hasErrors(),
                'getErrors' => $model->getErrors(),
            ]
        ];
    }

    public static function error($status, $msg)
    {
        Yii::$app->response->statusCode = $status;

        return [
            'status' => $status,
            'message' => $msg
        ];
    }
}
