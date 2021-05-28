<?php

namespace app\modules\v1\controllers;

use app\helpers\BehaviorsFromParamsHelper;
use app\helpers\ResponseHelper;
use app\models\farmasi\MasterBarang;
use app\models\farmasi\MasterJenis;
use app\models\farmasi\MasterSatuan;
use app\models\farmasi\MasterSubJenis;
use app\models\farmasi\MasterSupplier;
use app\models\farmasi\Penerimaan;
use app\models\farmasi\PenerimaanDetail;
use app\models\Status;
use Yii;
use yii\rest\ActiveController;

class PenerimaanController extends ActiveController
{
    public $modelClass = 'app\models\farmasi\Penerimaan';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors = BehaviorsFromParamsHelper::behaviors($behaviors);
        return $behaviors;
    }

    public function actionAll()
    {
        $listPenerimaan = Penerimaan::find()
            ->asArray()
            ->all();

        $list = [];
        foreach ($listPenerimaan as $Penerimaan) {
            $riwayat = $Penerimaan['riwayat'];
            $Penerimaan['riwayat'] = json_decode($riwayat);
            unset($Penerimaan['riwayat']);
            $list[] = $Penerimaan;
        }
        $count = count($listPenerimaan);
        $data = [
            "count" => $count,
            "penerimaan" => $list
        ];
        return ResponseHelper::success(Status::STATUS_OK, "Succeesfully", $data);
    }

    public function actionById()
    {
        $params = Yii::$app->request->post();
        $id_penerimaan = $params['id_penerimaan'];

        return PenerimaanController::getPenerimaanById($id_penerimaan);
    }

    public function actionAddPenerimaan()
    {
        $requestBody = json_decode(Yii::$app->request->getRawBody(), true);

        $created_by = $requestBody['created_by'];
        $tgl_sp = $requestBody['tgl_sp'];
        $no_sp = $requestBody['no_sp'];
        $id_supplier = $requestBody['id_supplier'];
        $tipe_pembelian = $requestBody['tipe_pembelian'];
        $is_cito = $requestBody['is_cito'];
        $total_sebelum_diskon = $requestBody['total_sebelum_diskon'];
        $total_diskon = $requestBody['total_diskon'];
        $total_setelah_diskon = $requestBody['total_setelah_diskon'];
        $is_ppn = $requestBody['is_ppn'];
        $total_ppn = $requestBody['total_ppn'];
        $total = $requestBody['total'];
        $id_apj = $requestBody['id_apj'];
        $id_pptk = $requestBody['id_pptk'];
        $catatan = $requestBody['catatan'];
        $id_unit_penerima = "103";

        $model = new Penerimaan();
        $model->created_by = $created_by;
        $model->updated_by = $created_by;
        $model->tgl_sp = $tgl_sp;
        $model->no_sp = $no_sp;
        $model->id_supplier = $id_supplier;
        $model->tipe_pembelian = $tipe_pembelian;
        $model->is_cito = $is_cito;
        $model->total_sebelum_diskon = $total_sebelum_diskon;
        $model->total_diskon = $total_diskon;
        $model->total_setelah_diskon = $total_setelah_diskon;
        $model->is_ppn = $is_ppn;
        $model->total_ppn = $total_ppn;
        $model->total = $total;
        $model->id_apj = $id_apj;
        $model->id_pptk = $id_pptk;
        $model->id_unit_penerima = $id_unit_penerima;

        $model->created_at = date('Y-m-d H:i:s');
        $model->updated_at = date('Y-m-d H:i:s');
        $model->is_deleted = false;
        $model->status = 0;
        $model->jenis = "NORMAL";
        $model->catatan = $catatan;

        if ($model->save(false)) {

            foreach ($requestBody['barang'] as $barang) {
                $modelDetail = new PenerimaanDetail();
                $modelDetail->is_active = true;
                $modelDetail->created_at = date('Y-m-d H:i:s');
                $modelDetail->updated_at = date('Y-m-d H:i:s');
                $modelDetail->created_by = $created_by;
                $modelDetail->updated_by = $created_by;

                $modelDetail->id_penerimaan = $model->id_penerimaan;

                $modelDetail->id_barang = $barang['id_barang'];
                $modelDetail->jumlah_kemasan = $barang['jumlah_kemasan'];
                $modelDetail->id_kemasan = $barang['id_kemasan'];
                $modelDetail->harga_kemasan = $barang['harga_kemasan'];
                $modelDetail->isi_per_kemasan = $barang['isi_per_kemasan'];
                $modelDetail->jumlah_total = $barang['jumlah_total'];
                $modelDetail->id_satuan = $barang['id_satuan'];
                $modelDetail->harga_satuan = $barang['harga_satuan'];
                $modelDetail->subtotal = $barang['subtotal'];
                $modelDetail->diskon_persen = $barang['diskon_persen'];
                $modelDetail->diskon_total = $barang['diskon_total'];
                $modelDetail->keterangan = $barang['keterangan'];
                $modelDetail->is_ppn = $barang['is_ppn'];
                $modelDetail->harga_beli_sekarang = $barang['harga_beli_sekarang'];
                $modelDetail->harga_beli_tertinggi = $barang['harga_beli_tertinggi'];
                $modelDetail->jumlah_diterima = $barang['jumlah_diterima'];

                $modelDetail->kon_harga_jual_satuan = "0.00";
                $modelDetail->kon_harga_modal_satuan = "0.00";
                $modelDetail->kon_harga_beli_pbf = "0.00";

                if ($modelDetail->save(false)) {
                } else {
                    return ResponseHelper::error(
                        Status::STATUS_BAD_REQUEST,
                        "Error"
                    );
                    break;
                }
            }
            return PenerimaanController::getPenerimaanById($model->id_penerimaan);
        } else {
            return ResponseHelper::error(
                Status::STATUS_BAD_REQUEST,
                "Error"
            );
        }
    }

    static function getPenerimaanById($id_penerimaan)
    {
        $one = Penerimaan::find()->alias("peng")
            ->select(["peng.*", "sup.nama_supplier"])
            ->leftJoin(MasterSupplier::tableName() . " as sup", "sup.id_supplier = peng.id_supplier")
            ->where(["peng.id_penerimaan" => $id_penerimaan])
            ->andWhere(["sup.is_active" => true])
            ->asArray()
            ->one();
        unset($one['riwayat']);

        $listPenerimaan = PenerimaanDetail::find()->alias("detail")
            ->leftJoin(Penerimaan::tableName() . " as adaan", "adaan.id_penerimaan::varchar = detail.id_penerimaan::varchar")
            ->where(["detail.id_penerimaan" => $id_penerimaan])
            ->asArray()
            ->all();

        $list = [];
        foreach ($listPenerimaan as $Penerimaan) {
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
                ->where(["bar.id_barang" => $Penerimaan['id_barang']])
                ->asArray()
                ->one();

            unset($listBarang['riwayat']);

            $riwayat = $Penerimaan['riwayat'];
            $Penerimaan['riwayat'] = json_decode($riwayat);
            unset($Penerimaan['riwayat']);
            $Penerimaan['barang'] = $listBarang;
            $list[] = $Penerimaan;
        }
        $count = count($listPenerimaan);
        $data = [
            "count" => $count,
            "penerimaan" => $one,
            "penerimaan_detail" => $list
        ];
        return ResponseHelper::success(Status::STATUS_OK, "Succeesfully", $data);
    }
}
