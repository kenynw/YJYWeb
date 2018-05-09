<?php

namespace backend\controllers;

use Yii;
use common\models\WeixinReply;
use common\models\WeixinReplySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\functions\Functions;
use yii\base\Object;
use common\components\WeixinService;

/**
 * WeixinReplyController implements the CRUD actions for WeixinReply model.
 */
class WeixinReplyController extends Controller
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
     * Lists all WeixinReply models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new WeixinReplySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single WeixinReply model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new WeixinReply model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new WeixinReply();
        $post = Yii::$app->request->post();        

        if ($post) {
            $post['WeixinReply']['reply'] = Functions::checkStr($post['WeixinReply']['reply']);
            
            if ($model->load($post) && $model->save()) {
                return $this->redirect(['index']);
            } else {
                return $this->render('_form', [
                    'model' => $model,
                ]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing WeixinReply model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->reply = htmlspecialchars_decode($model->reply);
        $post = Yii::$app->request->post();
        
        if ($post) {
            $post['WeixinReply']['reply'] = Functions::checkStr($post['WeixinReply']['reply']);
        
            if ($model->load($post) && $model->save()) {
                return $this->redirect(['index']);
            } else {
                return $this->renderAjax('_form', [
                    'model' => $model,
                ]);
            }
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing WeixinReply model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
    }

    /**
     * Finds the WeixinReply model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return WeixinReply the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WeixinReply::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    public function actionValidateForm () {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new WeixinReply();
        $model->load(Yii::$app->request->post());
        return \yii\widgets\ActiveForm::validate($model);
    }
    
    //获取素材列表
    public function actionMaterialList()
    {
        if ($_POST) {
            $page = $_POST['page'];            
            $type = $_POST['type'];            
            $service = new WeixinService();
            
            $totalCount = $service->getMaterialCount()["$type"."_count"];
            if ($type == 'image') {
                $pageSize = 15;
            } else if ($type == 'news') {
                $pageSize = 8;
            }
            $pageCount = intval(ceil($totalCount/$pageSize));
            $offset = ($page-1)*$pageSize;
            
            if ($page<1 || $page>$pageCount) {
                echo '0';
            }
            
            $materialList['list'] = $service->getBatchgetMaterial($type,$offset,$pageSize);
            if ($materialList['list']) {
                $materialList['type'] = $type;
                $materialList['pageMsg']['page'] = $page;
                $materialList['pageMsg']['pageCount'] = $pageCount;
                    
                $result = $this->renderPartial('_data.php', [
                    'materialList' => $materialList,
                ]);
                echo json_encode($result);
            } else {
                echo '0';
            }
        }
    }
}
