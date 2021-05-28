<?php

namespace app\modules\v1\controllers;

use Yii;
use app\helpers\BehaviorsFromParamsHelper;
use app\models\Status;
use app\helpers\ResponseHelper;
use app\models\farmasi\AuthAssignment;
use app\models\pegawai\Pegawai;
use app\models\pegawai\RiwayatPenempatan;
use app\models\sso\AknUser;
use yii\rest\ActiveController;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors = BehaviorsFromParamsHelper::behaviors($behaviors);
        return $behaviors;
    }

    public function actionGetUser()
    {
        $user = Yii::$app->user->identity;

        $authAssigment = null;
        $sso = null;
        $pegawai = Pegawai::find()
            ->where(["id_nip_nrp" => $user['username']])->one();

        if ($pegawai != null) {
            $sso = AknUser::find()
                ->where(["id_pegawai" => $pegawai->pegawai_id])->one();
        }

        if ($sso != null) {
            $authAssigment = AuthAssignment::find()
                ->where(["user_id" => $sso->userid])->one();
        }

        $data = [
            'user' => $user,
            'pegawai' => $pegawai,
            'sso' => $sso,
            'auth_assignment' => $authAssigment
        ];

        return ResponseHelper::success(Status::STATUS_OK, "Succeesfully", $data);
    }

    public function actionGetUserFarmasi()
    {

        $kepala = Pegawai::find()->alias("p")
            ->select(
                [
                    'p.pegawai_id',
                    'p.id_nip_nrp',
                    'p.nama_lengkap',
                    'p.gelar_sarjana_depan',
                    'p.gelar_sarjana_belakang'
                ]
            )
            ->leftJoin(RiwayatPenempatan::tableName() . " as up", "up.id_nip_nrp::varchar = p.id_nip_nrp::varchar")
            ->where(["up.penempatan" => "38"])
            ->andWhere(['status_aktif' => "1"])
            ->asArray()->one();

        $ppk = Pegawai::find()->alias("p")
            ->select(
                [
                    'p.pegawai_id',
                    'p.id_nip_nrp',
                    'p.nama_lengkap',
                    'p.gelar_sarjana_depan',
                    'p.gelar_sarjana_belakang'
                ]
            )
            ->leftJoin(RiwayatPenempatan::tableName() . " as up", "up.id_nip_nrp::varchar = p.id_nip_nrp::varchar")
            ->where(["up.penempatan" => "2"])
            ->andWhere(['status_aktif' => "1"])
            ->asArray()->one();

        $userFarmasi = Pegawai::find()->alias("p")
            ->select(
                [
                    'p.pegawai_id',
                    'p.id_nip_nrp',
                    'p.nama_lengkap',
                    'p.gelar_sarjana_depan',
                    'p.gelar_sarjana_belakang'
                ]
            )
            ->leftJoin(RiwayatPenempatan::tableName() . " as up", "up.id_nip_nrp::varchar = p.id_nip_nrp::varchar")
            ->where(["up.unit_kerja" => "38"])
            ->andWhere(['status_aktif' => "1"])
            ->asArray()->all();

        $data = [
            "ppk" => $ppk,
            "kepala_instalasi" => $kepala,
            "anggota_instalasi" => $userFarmasi
        ];

        return ResponseHelper::success(Status::STATUS_OK, "Succeesfully", $data);
    }
}
