<?php

namespace app\modules\v1\controllers;

use app\helpers\BehaviorsFromParamsHelper;
use app\helpers\ResponseHelper;
use app\models\farmasi\MasterSatuan;
use app\models\farmasi\MasterSupplier;
use app\models\farmasi\Pengadaan;
use app\models\Status;
use yii\rest\ActiveController;

class SupplierController extends ActiveController
{
    public $modelClass = 'app\models\farmasi\MasterSupplier';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors = BehaviorsFromParamsHelper::behaviors($behaviors);
        return $behaviors;
    }

    public function actionAll()
    {
        $listPengadaan = Pengadaan::find()
            ->where(["is_active" => true])
            ->asArray()
            ->all();

        $listSatuan = MasterSatuan::find()
            ->select(["id_satuan", "nama_satuan", "keterangan"])
            ->where(["is_active" => true])
            ->asArray()->all();

        $listSupplier = MasterSupplier::find()
            ->select(["id_supplier", "nama_supplier", "alamat", "telepon"])
            ->where(["is_active" => true])
            ->asArray()->all();

        $count = count($listPengadaan);

        $data = [
            "satuan" => $listSatuan,
            "supplier" => $listSupplier,
            "count" => $count
        ];
        return ResponseHelper::success(Status::STATUS_OK, "Succeesfully", $data);
    }
}
