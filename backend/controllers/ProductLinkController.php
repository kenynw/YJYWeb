<?php

namespace backend\controllers;

use Yii;
use common\models\ProductLink;
use common\models\ProductLinkSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProductLinkController implements the CRUD actions for ProductLink model.
 */
class ProductLinkController extends Controller
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
     * Lists all ProductLink models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductLinkSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        //返利链接类型
        $linkType = ['1'=>'淘宝','2'=>'京东','3'=>'亚马逊'];
        
        //有导入数据的管理员
        $adminArr = ProductLink::find()->alias('pl')->select("a.id,a.username")->innerJoin('yjy_admin a','pl.admin_id = a.id')->groupBy('a.id')->asArray()->all();
        $adminList = array_column($adminArr,'username','id');
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'linkType' => $linkType,
            'adminList' => $adminList
        ]);
    }

    /**
     * Displays a single ProductLink model.
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
     * Creates a new ProductLink model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductLink();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing ProductLink model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing ProductLink model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        $url = Yii::$app->request->referrer.'#comment';

        return $this->redirect($url);
    }
    
    //批量删除
    public function actionDeleteAll(){
        if($_POST['id']){
            foreach($_POST['id'] as $val){
                $this->findModel($val)->delete();
            }
            echo 1;
        }
    }

    /**
     * Finds the ProductLink model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductLink the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductLink::findOne($id)) !== null) {
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
    
        if($model = ProductLink::findOne($id)){
            $status = $status == 1 ? 0 : 1;
    
            $model->$type = $status;
            $model->save(false);
    
            $data['status'] = "1";
        }
    
        echo json_encode($data);
    }
    
    //批量改变状态
    public function actionUpdateAll(){
        $update = ProductLink::updateAll(['status'=> 1],"status = 0 AND url = ''");
        if ($update) {
            echo '1';
        }
    }
}
