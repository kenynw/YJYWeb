<?php

namespace backend\controllers;

use Yii;
use common\models\SkinBaike;
use common\models\SkinBaikeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Skin;
use yii\base\Object;
use common\models\SkinBaikeAnswer;

/**
 * SkinBaikeController implements the CRUD actions for SkinBaike model.
 */
class SkinBaikeController extends Controller
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
                    'imagePathFormat' => $path."baike/{yyyy}{mm}{dd}/{time}{rand:6}",
                ]
            ]
        ];
    }

    /**
     * Lists all SkinBaike models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SkinBaikeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SkinBaike model.
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
     * Creates a new SkinBaike model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SkinBaike();
        //肤质列表
        $skinArr = Skin::find()->asArray()->all();
        $skinList = [];
        foreach ($skinArr as $key=>$val){
            $skinList[$val['id']] = $val['skin'].'('.$val['explain'].')';
        }

        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->save()) {
            //保存答案
            $answer = new SkinBaikeAnswer();
            $answer->qid = $model->id;
            $answer->content = $post['SkinBaike']['answer'];
            $answer->shortcontent = $post['SkinBaike']['shortanswer'];
            $answer->picture = $post['SkinBaike']['picture'];
            $answer->save();
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('create', [
                'model' => $model,
                'skinList' => $skinList
            ]);
        }
    }

    /**
     * Updates an existing SkinBaike model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        //肤质列表
        $skinArr = Skin::find()->asArray()->all();
        $skinList = [];
        foreach ($skinArr as $key=>$val){
            $skinList[$val['id']] = $val['skin'].'('.$val['explain'].')';
        }

        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->save()) {
            //保存答案
            $answer = SkinBaikeAnswer::find()->where("qid = $model->id")->one();
            $answer->content = $post['SkinBaike']['answer'];
            $answer->shortcontent = $post['SkinBaike']['shortanswer'];
            $answer->picture = $post['SkinBaike']['picture'];
            $answer->save();
            return $this->redirect(['index']);
        } else {
            $model->answer = $model->skinBaikeAnswer->content;
            $model->shortanswer = $model->skinBaikeAnswer->shortcontent;
            $model->picture = $model->skinBaikeAnswer->picture;
            return $this->renderAjax('update', [
                'model' => $model,
                'skinList' => $skinList
            ]);
        }
    }

    /**
     * Deletes an existing SkinBaike model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        //删除对应答案
        $answer = SkinBaikeAnswer::find()->where("qid = $id")->one();
        $answer->delete();

        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
    }

    /**
     * Finds the SkinBaike model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SkinBaike the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SkinBaike::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
