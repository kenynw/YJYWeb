<?php

namespace backend\controllers;

use Yii;
use common\models\ProductComponent;
use common\models\ProductComponentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\ProductRelate;
use yii\base\Object;

/**
 * ProductComponentController implements the CRUD actions for ProductComponent model.
 */
class ProductComponentController extends Controller
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

    /**
     * Lists all ProductComponent models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductComponentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $model = new ProductComponent();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }

    /**
     * Displays a single ProductComponent model.
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
     * Creates a new ProductComponent model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductComponent();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing ProductComponent model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            
            $url = Yii::$app->request->referrer;
            return $this->redirect($url);
        } else {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing ProductComponent model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        
        //删除成分关系
        ProductRelate::deleteAll('component_id = :component_id', [':component_id' => $id]);

        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
    }

    public function actionCheckName($id,$name){

        if($id){
            $cond = [ 'and',['!=', 'id', $id], 'name = "$name"'];
        }else{
            $cond = [ 'name' => $name];
        }

        $model = ProductComponent::find()->where($cond)->one();
        if($model){
            echo 1;
        }
    }

    public function actionUpdateDemo(){

        $id = Yii::$app->request->post("id");
        if($id){
            $model = $this->findModel($id);
        }else{
            $model = new ProductComponent();
        }

        $data = $this->renderPartial('_form', [
            'model' => $model,
        ]);
        echo json_encode($data);
    }

    public function actionUpdateRecommend(){

        $id = Yii::$app->request->post("id");
        $recommend = Yii::$app->request->post("recommend");
        $type = Yii::$app->request->post("type");

        $data['status'] = "0";

        if($model = ProductComponent::findOne($id)){
            $recommend = $recommend == 1 ? 0 : 1;
            $model->$type = $recommend;
            $model->save(false);

            $data['status'] = "1";
        }

        echo json_encode($data);
    }

    /**
     * Finds the ProductComponent model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductComponent the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductComponent::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    public function actionValidateForm ($id = '') {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if ($id) {
            $model = $this->findModel($id);
        } else {
            $model = new ProductComponent();
        }
        
        $model->load(Yii::$app->request->post());
        return \yii\widgets\ActiveForm::validate($model);
    }
}
