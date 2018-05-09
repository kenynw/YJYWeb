<?php

namespace m\controllers;

use Yii;
use yii\web\Controller;
use m\models\WebPage;
use yii\web\NotFoundHttpException;
use common\functions\Functions;
/**
 * App controller
 */
class AsaController extends BaseController
{
    /**
     * [actionLanding APP跳转落地页]
     * @return [type] [description]
     */
    public function actionDownload()
    {
        $id         = Yii::$app->request->get('id','');
        $hid        = Yii::$app->request->get('hid','');
        $type       = Yii::$app->request->get('type','');
        $relation   = Yii::$app->request->get('unlrelation','');
        $unltype    = Yii::$app->request->get('unltype');

        $id         = intval($id);
        $hid        = intval($hid);
        $type       = intval($type);
        $relation   = Functions::checkStr($relation);
        $unltype    = Functions::checkStr($type);

        $downloadUrl    = Yii::$app->urlManager->createUrl(['site/download-guide','id'=>$id,'hid'=> $hid,'type'=>$type]);

        return $this->renderPartial('guide.htm', [
            'downloadUrl'=> $downloadUrl,
            'type'       => $type,
            'relation'   => $relation,
            'GLOBALS'    => $this->GLOBALS,
        ]);
    }
    /**
     * [actionLanding APP跳转落地页]
     * @return [type] [description]
     */
    public function actionDownload2()
    {

        return $this->renderPartial('download.htm', [
            'GLOBALS'    => $this->GLOBALS,
        ]);
    }
}
