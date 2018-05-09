<?php

namespace backend\controllers;

use Yii;
use common\models\Banner;
use common\models\BannerSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\base\Object;

class BannerController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actions()
    {
        $path = Yii::$app->params['isOnline'] ? "uploads/" : "cs/uploads/";

        return [
            'uploads'=>[
                'class' => 'common\widgets\file_upload\UploadAction',
                'config' => [
                    'imagePathFormat' => $path."banner/{yyyy}{mm}{dd}/{time}{rand:6}",
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        $searchModel = new BannerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

//         //修改过期时间的状态
//         $time = strtotime(date("Y-m-d",time()));
//         $banner = Banner::updateAll(['status'=> 0],"end_time <= $time");
        
// 		//过期改成正常后的状态
//         $time = strtotime(date("Y-m-d",time()));
//         $banner = Banner::updateAll(['status'=> 1],"end_time > $time");

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new Banner();

        $data  = '';
        if ($data = Yii::$app->request->post()) {
            $data['Banner']['start_time'] = strtotime($data['Banner']['start_time']);
            $data['Banner']['end_time']   = strtotime($data['Banner']['end_time']);

            //文章、产品ID
            if($data['Banner']['type'] != 1){
                $data['Banner']['relation_id'] = $data['Banner']['url'];
            }
        }

        if ($model->load($data) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $data  = '';
        if ($data = Yii::$app->request->post()) {
            $data['Banner']['start_time'] = strtotime($data['Banner']['start_time']);
            $data['Banner']['end_time']   = strtotime($data['Banner']['end_time']);

            //文章、产品ID
            if($data['Banner']['type'] == 2 || $data['Banner']['type'] == 3 || $data['Banner']['type'] == 5){
                $data['Banner']['relation_id'] = $data['Banner']['url'];
            } elseif ($data['Banner']['type'] == 4) {
                $data['Banner']['relation_id'] = $data['Banner']['url'] = '';
            }
        }

        if ($model->load($data) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    //改变状态
    public function actionChangeStatus()
    {
        $id = Yii::$app->request->post("id");
        $status = Yii::$app->request->post("status");
        $type = Yii::$app->request->post("type");

        $data['status'] = "0";

        if($model = Banner::findOne($id)){
            $status = $status == 1 ? 0 : 1;

            //上线(如果过期则再当前时间 +1天)
//             if($status == 1){
//                 $now = date("Y-m-d");
//                 if ($model->end_time <= strtotime($now)) {
//                     $model->end_time = strtotime("$now +1 day");
//                 }
//             }

            $model->$type = $status;
            $model->save(false);

            $data['status'] = "1";
        }

        echo json_encode($data);
    }

    //删除banner改为修改状态
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Banner::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    public function actionValidateForm () {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new Banner();
        $model->load(Yii::$app->request->post());
        return \yii\widgets\ActiveForm::validate($model);
    }
}
