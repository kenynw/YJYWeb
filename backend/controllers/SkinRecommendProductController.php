<?php

namespace backend\controllers;

use Yii;
use common\models\SkinRecommendProduct;
use common\models\SkinRecommendProductSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Skin;
use backend\models\CommonFun;

/**
 * SkinRecommendProductController implements the CRUD actions for SkinRecommendProduct model.
 */
class SkinRecommendProductController extends Controller
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
     * Lists all SkinRecommendProduct models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SkinRecommendProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        //分类
        $cateList = CommonFun::getCateList($where = 'parent_id <> 0');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cateList' => $cateList,
        ]);
    }

    /**
     * Displays a single SkinRecommendProduct model.
     * @param integer $skin_id
     * @param integer $cate_id
     * @param string $product_id
     * @return mixed
     */
    public function actionView($skin_id, $cate_id, $product_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($skin_id, $cate_id, $product_id),
        ]);
    }

    /**
     * Creates a new SkinRecommendProduct model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SkinRecommendProduct();
        //肤质
        $skinid = Skin::find()->asArray()->all();
        $skinList = [];
        foreach ($skinid as $key=>$val) {
            $skinList[$val['id']] = $val['skin'].'('.$val['explain'].')';
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $model->skin_name = Skin::findOne($model->skin_id)->skin;
            $model->save();
            
            $url = Yii::$app->request->referrer;
            return $this->redirect($url);
        } else {
            //分类
            $cateList = CommonFun::getCateList($where = 'parent_id <> 0');
            return $this->renderAjax('create', [
                'model' => $model,
                'skinList' => $skinList,
                'cateList' => $cateList,
            ]);
        }
    }

    /**
     * Updates an existing SkinRecommendProduct model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $skin_id
     * @param integer $cate_id
     * @param string $product_id
     * @return mixed
     */
    public function actionUpdate($skin_id, $cate_id, $product_id)
    {
        $model = $this->findModel($skin_id, $cate_id, $product_id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            //分类
            $cateList = CommonFun::getCateList($where = 'parent_id <> 0');
            return $this->renderAjax('update', [
                'model' => $model,
                'cateList' => $cateList,
            ]);
        }
    }

    /**
     * Deletes an existing SkinRecommendProduct model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $skin_id
     * @param integer $cate_id
     * @param string $product_id
     * @return mixed
     */
    public function actionDelete($skin_id, $cate_id, $product_id)
    {
        $model = $this->findModel($skin_id, $cate_id, $product_id);
        $model->status = 0;
        $model->save();

        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
    }
    
    //批量删除
    public function actionDeleteAll()
    {
        $post = Yii::$app->request->post();
        $items = $post['id'];
        foreach ($items as $key=>$val) {
            $model = $this->findModel($val['skin_id'], $val['cate_id'], $val['product_id']);
            $model->status = 0;
            $model->save();
        } 

        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
    }

    /**
     * Finds the SkinRecommendProduct model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $skin_id
     * @param integer $cate_id
     * @param string $product_id
     * @return SkinRecommendProduct the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($skin_id, $cate_id, $product_id)
    {
        if (($model = SkinRecommendProduct::findOne(['skin_id' => $skin_id, 'cate_id' => $cate_id, 'product_id' => $product_id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
