<?php

namespace app\modules\v1\controllers;

use app\helpers\BehaviorsFromParamsHelper;
use app\helpers\ResponseHelper;
use app\models\farmasi\MasterBarang;
use app\models\farmasi\MasterJenis;
use app\models\farmasi\MasterKemasan;
use app\models\farmasi\MasterSatuan;
use app\models\farmasi\MasterSubJenis;
use app\models\Status;
use yii\rest\ActiveController;

class BarangController extends ActiveController
{
    public $modelClass = 'app\models\farmasi\MasterBarang';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors = BehaviorsFromParamsHelper::behaviors($behaviors);
        return $behaviors;
    }

    public function actionAll()
    {
        $listBarang = MasterBarang::find()->alias('bar')
            ->select([
                "bar.*",
                "jenis.nama_jenis",
                "subjenis.nama_sub_jenis",
                "sat.nama_satuan",
                "kem.nama_satuan as nama_kemasan"
            ])
            ->leftJoin(MasterJenis::tableName() . " as jenis", "jenis.id_jenis::varchar = bar.id_jenis::varchar")
            ->leftJoin(MasterSubJenis::tableName() . " as subjenis", "subjenis.id_sub_jenis::varchar = bar.id_sub_jenis::varchar")
            ->leftJoin(MasterSatuan::tableName() . " as sat", "sat.id_satuan::varchar = bar.id_satuan::varchar")
            ->leftJoin(MasterSatuan::tableName() . " as kem", "kem.id_satuan::varchar = bar.id_kemasan::varchar")
            ->where(['!=', "bar.harga_kemasan", null])
            ->orWhere(['!=',  "bar.harga_kemasan", 0])
            ->andWhere(["bar.is_deleted" => false])
            ->asArray()
            ->all();

        $list = [];
        foreach ($listBarang as $barang) {
            $riwayat = $barang['riwayat'];
            $barang['riwayat'] = json_decode($riwayat);
            $list[] = $barang;
        }
        $count = count($listBarang);
        $data = [
            "count" => $count,
            "barang" => $list
        ];
        return ResponseHelper::success(Status::STATUS_OK, "Succeesfully", $data);
    }
}
