<?php

namespace backend\controllers;

use Yii;
use common\models\Skin;
use common\models\SkinSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\ProductComponent;
use yii\base\Object;
use common\models\ProductCategory;
use common\models\SkinRecommend;
use common\models\ProductRelate;

/**
 * SkinController implements the CRUD actions for Skin model.
 */
class SkinController extends Controller
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
     * Lists all Skin models.
     * @return mixed
     */
    public function actionIndex()
    {   
        $searchModel = new SkinSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Skin model.
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
     * Creates a new Skin model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Skin();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Skin model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        $model2 = new SkinRecommend();
        $cateList = ProductCategory::find()->select('id,cate_name')->where('parent_id <> 0')->asArray()->all();
        
        //处理数据
        $post = '';
        
        if (Yii::$app->request->post()) {
            $post = Yii::$app->request->post();
            
            //处理推荐成分
            foreach ($cateList as $key=>$val) {
                if ($key <= 7) {
                    //原推荐成分
                    $oldModel = SkinRecommend::find()->where("category_id = {$val['id']} AND skin_id = $id")->one();                                                            
                    if (!empty($oldModel)) {
                        $oldModelArr = explode(",",$oldModel->reskin);
                    } else {
                        $oldModelArr = [];
                    }
                    if (!empty($post['SkinRecommend']['reskin'.($key+1)])) {
                        $newModelArr = $post['SkinRecommend']['reskin'.($key+1)];
                    } else {
                        $newModelArr = [];
                    }
                    //新旧推荐成分数组交集
                    $sameArr = array_intersect($oldModelArr,$newModelArr);                    
                    //去掉两数组交集后的新旧推荐成分数组       
                    $oldModelArr = array_diff($oldModelArr, $sameArr); 
                    $newModelArr = array_diff($newModelArr, $sameArr);                                   
                    //处理相应的肤质成分推荐产品状态                   
                    if (!empty($oldModelArr)) {
                        //被删
                        $params = ['skin'=>$model->skin,'categoryId' => $val['id'],'relate'=>$oldModelArr];
                        \common\functions\Skin::deleteSkinRecommendProduct($params);
                    }
                    if (!empty($newModelArr)) {
                        //新增
                        $params = ['skin'=>$model->skin,'categoryId' => $val['id'],'relate'=>$newModelArr];
                        \common\functions\Skin::insertSkinRecommendProduct($params);
                    }
                }
            }

            //删除后保存新数据
            SkinRecommend::deleteAll(['skin_id' => $id]);
            foreach ($cateList as $key=>$val) {
                if ($key <= 7 && (!empty($post['SkinRecommend']['reskin'.($key+1)]) || !empty(trim($post['SkinRecommend']['copy'][$key])) || !empty($post['SkinRecommend']['noreskin'.($key+1)]))) {

                    $model2 = new SkinRecommend();
                    $model2->category_id = $val['id'];
                    $model2->skin_id = $id;
                    $model2->skin_name = $model->skin;

                    if (!empty($post['SkinRecommend']['reskin'.($key+1)])) {
                        $model2->reskin = join(",",$post['SkinRecommend']['reskin'.($key+1)]);
                    }     
                    
                    if (!empty($post['SkinRecommend']['noreskin'.($key+1)])) {
                        $model2->noreskin = join(",",$post['SkinRecommend']['noreskin'.($key+1)]);
                    }
                    
                    $model2->copy = trim($post['SkinRecommend']['copy'][$key]);

                    $model2->save();
                }                
            }
        }

        if ($model->load($post) && $model->save()) {
            return $this->redirect('index');
        } else {
            return $this->render('update', [
                'model' => $model,
                'model2' => $model2,
                'cateList' => $cateList,
            ]);
        }
    }

    /**
     * Deletes an existing Skin model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    //获取成分推荐产品
    public function actionRefresh($skin)
    {
        ini_set("max_execution_time", 2400);
        \common\functions\Skin::saveSkinRecommendProduct($skin);
    
        return $this->redirect(['index']);
    }

    /**
     * Finds the Skin model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Skin the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Skin::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    //搜索成分
    public function actionSearchComponent ($q){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!$q) {
            return $out;
        }
    
        $data = ProductComponent::find()
            ->select('id, name as text')
            ->andFilterWhere(['like', 'name', $q])
            ->limit(20)
            ->asArray()
            ->all();
    
        $out['results'] = array_values($data);
    
        return $out;
    }
}
