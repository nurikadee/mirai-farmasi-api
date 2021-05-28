<?php

namespace app\modules\v1\controllers;

use Yii;
use app\helpers\ResponseHelper;
use app\models\farmasi\AuthAssignment;
use app\models\mirai\TokenUser;
use app\models\pegawai\Pegawai;
use app\models\sso\AknUser;
use app\models\User;
use app\models\Status;
use yii\rest\Controller;

class AuthController extends Controller
{
    protected function verbs()
    {
        return [
            'signup' => ['POST'],
            'login' => ['POST'],
        ];
    }

    public static function actionSaveToken()
    {
        $params = Yii::$app->request->post();
        $username = $params['username'];
        $device_id = $params['device_id'];
        $token = $params['token'];

        if (empty($token) || empty($device_id)) {
            return ResponseHelper::error(
                Status::STATUS_BAD_REQUEST,
                "Inputan tidak lengkap"
            );
        }

        $device = TokenUser::find()->where(['device_id' => $device_id])->one();

        if (is_null($device)) {
            $model = new TokenUser();
            $model->username = $username;
            $model->device_id = $device_id;
            $model->token = $token;

            if ($model->save(false)) {
                return ResponseHelper::success(Status::STATUS_OK, "Successfully", null);
            } else {
                return ResponseHelper::error(Status::STATUS_BAD_REQUEST, "Error Token");
            }
        } else {
            $device->username = $username;
            $device->device_id = $device_id;
            $device->token = $token;


            if ($device->save(false)) {
                return ResponseHelper::success(Status::STATUS_OK, "Successfully", null);
            } else {
                return ResponseHelper::error(Status::STATUS_BAD_REQUEST, "Error Token");
            }
        }

        return ResponseHelper::error(Status::STATUS_BAD_REQUEST, "Error Token");
    }

    public static function actionLogin()
    {
        $params = Yii::$app->request->post();
        $username = $params['username'];
        $password = $params['password'];

        if (empty($username)) {
            return ResponseHelper::error(
                Status::STATUS_BAD_REQUEST,
                "Username tidak boleh kosong."
            );
        }

        if (empty($password)) {
            return ResponseHelper::error(
                Status::STATUS_BAD_REQUEST,
                "Password tidak boleh kosong."
            );
        }

        return AuthController::login($username, $password);
    }

    static function login($username, $password)
    {
        $user = User::findByUsername($username);
        if ($user != null) {
            if ($user->validatePassword($password)) {
                if (isset($params['consumer'])) $user->consumer = $params['consumer'];
                if (isset($params['access_given'])) $user->access_given = $params['access_given'];

                Yii::$app->response->statusCode = Status::STATUS_FOUND;
                $user->generateAuthKey();
                $user->save();

                $authAssigment = null;
                $sso = null;
                $pegawai = Pegawai::find()
                    ->where(["id_nip_nrp" => $username])->one();

                if ($pegawai != null) {
                    $sso = AknUser::find()
                        ->where(["id_pegawai" => $pegawai->pegawai_id])->one();
                }

                if ($sso != null) {
                    $authAssigment = AuthAssignment::find()
                        ->where(["user_id" => $sso->userid])->one();
                }

                return ResponseHelper::success(
                    Status::STATUS_OK,
                    "Login Succeed",
                    [
                        'user' => User::findByUsername($user->username),
                        'pegawai' => $pegawai,
                        'sso' => $sso,
                        'auth_assignment' => $authAssigment
                    ]
                );
            } else {
                return ResponseHelper::error(
                    Status::STATUS_UNAUTHORIZED,
                    "Username dan password tidak cocok!"
                );
            }
        } else {
            return AuthController::signup($username, $password);
        }
    }

    static function signup($username, $password)
    {
        $pegawai = Pegawai::find()
            ->where(["id_nip_nrp" => $username])->one();

        if ($pegawai != null) {

            $model = new User();
            $model->username = $username;

            $model->setPassword($password);
            $model->generateAuthKey();
            $model->status = User::STATUS_ACTIVE;

            if ($model->save(false)) {
                return AuthController::login($username, $password);
            } else {
                return ResponseHelper::error(
                    Status::STATUS_UNAUTHORIZED,
                    "Username dan password tidak cocok!"
                );
            }
        } else {
            return ResponseHelper::error(
                Status::STATUS_UNAUTHORIZED,
                "Aplikasi ini hanya untuk lingkungan internal RSUD Arifin Achmad Pekanbaru!"
            );
        }
    }
}
