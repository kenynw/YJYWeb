<?php

namespace backend\controllers;

use Yii;
use common\models\ArticleCategory;
use common\models\ArticleCategorySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Article;

/**
 * ArticleCategoryController implements the CRUD actions for ArticleCategory model.
 */
class ArticleCategoryController extends Controller
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
                    'imagePathFormat' => $path."article_category/{yyyy}{mm}{dd}/{time}{rand:6}",
                ]
            ]
        ];
    }

    /**
     * Lists all ArticleCategory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ArticleCategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $model = new ArticleCategory();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model
        ]);
    }

    /**
     * Displays a single ArticleCategory model.
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
     * Creates a new ArticleCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ArticleCategory();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index','parent_id'=>$model->parent_id]);
        } else {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing ArticleCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index','parent_id'=>$model->parent_id]);
        } else {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing ArticleCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {        
        //目前只有二级分类能删除，删除后该分类下的文章归到其一级分类中
        $model = $this->findModel($id);
        $parent_id = $model->parent_id;
        Article::updateAll(['cate_id'=> $parent_id],"cate_id = $id");
        $model->delete();

        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
    }

    //改变状态
    public function actionChangeStatus()
    {
        $id = Yii::$app->request->post("id");
        $status = Yii::$app->request->post("status");
        $type = Yii::$app->request->post("type");

        $data['status'] = "0";

        if($model = ArticleCategory::findOne($id)){
            $status = $status == 1 ? 0 : 1;
            $model->$type = $status;
            $model->save(false);

            $data['status'] = "1";
        }

        echo json_encode($data);
    }

    //获取二级分类列表
    public function actionGetCateList($cate_id){
        if($cate_id){
            $cate_list = ArticleCategory::find()->where(['parent_id'=>$cate_id])->asArray()->all();
            $str = "<option value=''>--- 请选择 ---</option>";
            if($cate_list){
                foreach($cate_list as $val){
                    $str .= "<option value='".$val['id']."'>".$val['cate_name']."</option>";
                }
            }else{
                $str = "<option value=''>--- 请选择 ---</option>";
            }
        }else{
            $str = "<option value=''>--- 请选择 ---</option>";
        }

        echo $str;
    }

    protected function findModel($id)
    {
        if (($model = ArticleCategory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
