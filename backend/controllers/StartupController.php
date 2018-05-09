<?php

namespace backend\controllers;

use Yii;
use common\models\Startup;
use common\models\StartupSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\base\Object;

/**
 * StartupController implements the CRUD actions for Startup model.
 */
class StartupController extends Controller
{
    /**
     * @inheritdoc
     */
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
                    'imagePathFormat' => $path."startup/{yyyy}{mm}{dd}/{time}{rand:6}",
                ]
            ]
        ];
    }    

    /**
     * Lists all Startup models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new StartupSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        //过期后的状态
        $time = time();
        Startup::updateAll(['status'=> 0],"end_time < $time");
        
		//过期改成正常后的状态
        $time = time();
        Startup::updateAll(['status'=> 1],"end_time >= $time");

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Startup model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Startup model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Startup();

        $data  = '';
        if ($data = Yii::$app->request->post()) {
            $data['Startup']['start_time'] = strtotime($data['Startup']['start_time']);
            $data['Startup']['end_time']   = strtotime($data['Startup']['end_time']);
        
            //相关ID
            if($data['Startup']['type'] != 0){
                $data['Startup']['relation_id'] = $data['Startup']['url'];
            }
            
        }
        
        if ($model->load($data) && $model->save()) {
            //只能一个上架
            if ($model->status > 0) {
                Startup::updateAll(['status'=> 0],"status = '1' AND id <> $model->id");
            }
            
            $url = Yii::$app->request->referrer;
            return $this->redirect($url);
        } else {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Startup model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        $data  = '';
        if ($data = Yii::$app->request->post()) {
            $data['Startup']['start_time'] = strtotime($data['Startup']['start_time']);
            $data['Startup']['end_time']   = strtotime($data['Startup']['end_time']);
        
            //相关ID
            if($data['Startup']['type'] != 0){
                $data['Startup']['relation_id'] = $data['Startup']['url'];
            }
        }

        if ($model->load($data) && $model->save()) {
            //只能一个上架
            if ($model->status > 0) {
                Startup::updateAll(['status'=> 0],"status = '1' AND id <> $model->id");
            }
            
            $url = Yii::$app->request->referrer;
            return $this->redirect($url);
        } else {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Startup model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        $url = Yii::$app->request->referrer;

        return $this->redirect($url);
    }

    /**
     * Finds the Startup model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Startup the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Startup::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    //改变状态
    public function actionChangeStatus()
    {
        $id = Yii::$app->request->post("id");
        $status = Yii::$app->request->post("status");
        $type = Yii::$app->request->post("type");
    
        $data['status'] = "0";
    
        if($model = Startup::findOne($id)){
            $status = $status == 1 ? 0 : 1;
            
            $model->$type = $status;
            $model->save(false);
            
            //只能一个上架
            if ($model->status > 0) {
                Startup::updateAll(['status'=> 0],"status = '1' AND id <> $model->id");
            }
    
            $data['status'] = "1";
        }
    
        echo json_encode($data);
    }
    
    public function actionValidateForm () {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new Startup();
        $model->load(Yii::$app->request->post());
        return \yii\widgets\ActiveForm::validate($model);
    }
}
