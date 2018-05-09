<?php
namespace api\controllers;

use yii;
use yii\web\Controller;
use common\components\Timingtaskcom;

class AutomanController extends Controller
{
    public function actionIndex(){
        $sql = "SELECT id, tb_goods_id  FROM {{%product_link}} WHERE url = '' AND type =1 AND status = 1 LIMIT 10";
        $results = Yii::$app->db->createCommand($sql)->queryAll();
        return json_encode($results);
    }

    public function actionJd(){
        $sql = "SELECT id, tb_goods_id  FROM {{%product_link}} WHERE url = '' AND type =2 AND status = 1";
        $results = Yii::$app->db->createCommand($sql)->queryAll();
        return json_encode($results);
    }
}
?>