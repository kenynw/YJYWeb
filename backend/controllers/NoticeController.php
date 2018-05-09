<?php

namespace backend\controllers;

use Yii;
use common\models\Notice;
use common\models\NoticeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\NoticeSystemSearch;
use common\models\NoticeSystem;
use common\functions\NoticeFunctions;

/**
 * NoticeController implements the CRUD actions for Notice model.
 */
class NoticeController extends Controller
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

    //常规通知
    /**
     * Lists all Notice models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new NoticeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
    
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect('index');
        } else {
            return $this->renderAjax('_form', [
                'model' => $model,
            ]);
        }
    }
    
    protected function findModel($id)
    {
        if (($model = Notice::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    //活动通知
    public function actionIndexUser()
    {
        Yii::$app->cache->flush();
        $searchModel = new NoticeSystemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $model = new NoticeSystem();
        return $this->render('index-user', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }
    
    public function actionCreateUser()
    {
        $model = new NoticeSystem();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index-user']);
        } else {
            return $this->renderAjax('_form', [
                'model' => $model,
            ]);
        }
    }
    
    public function actionUpdateUser($id)
    {
        $model = $this->findModelUser($id);
    
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index-user']);
        } else {
            return $this->renderAjax('_form', [
                'model' => $model,
            ]);
        }
    }
    
    public function actionDeleteUser($id)
    {
        $this->findModelUser($id)->delete();
    
        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
    }
    
    public function actionSend($id)
    {
        $model = $this->findModelUser($id);
        $model->status = 1;
        if ($model->save()) {
            //推送通知
            NoticeFunctions::JPushAll(['id' => $model->id, 'content' => $model->content]);
            return $this->redirect(['index-user']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    
    protected function findModelUser($id)
    {
        if (($model = NoticeSystem::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    public function actionValidateForm () {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new NoticeSystem();
        $model->load(Yii::$app->request->post());
        return \yii\widgets\ActiveForm::validate($model);
    }

}
