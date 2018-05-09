<?php

namespace backend\controllers;

use Yii;
use common\models\ProductCategory;
use common\models\ProductCategorySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\ProductDetailsSearch;
use yii\base\Object;
use backend\models\CommonFun;
use common\models\ProductDetails;
use common\models\AskQuestion;

/**
 * ProductCategoryController implements the CRUD actions for ProductCategory model.
 */
class ProductCategoryController extends Controller
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
                    // 'imagePathFormat' => "/image/{yyyy}{mm}{dd}/{time}{rand:6}",
                    //'imagePathFormat' => "../../frontend/web/uploads/cate_img/{yyyy}{mm}{dd}/{time}{rand:6}",//上传图片的路径
                    'imagePathFormat' => $path."cate_img/{yyyy}{mm}{dd}/{time}{rand:6}",
                    // 'imagePathFormat' => "/uploads/banner/{time}{rand:6}",
                ]
            ]
        ];
    }

    /**
     * Lists all ProductCategory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductCategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $model = new ProductCategory();
        //一级分类数组
        $parentArr = CommonFun::getKeyValArr($model, 'id', 'cate_name','parent_id = 0');
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'parentArr' => $parentArr
        ]);
    }

    /**
     * Displays a single ProductCategory model.
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
     * Creates a new ProductCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductCategory();
        //一级分类数组
        $parentArr = CommonFun::getKeyValArr($model, 'id', 'cate_name','parent_id = 0');
        $parentArr = ['0'=> '请选择（不选择则默认创建为一级分类哦）'] + $parentArr;
        $post = Yii::$app->request->post();
        
        if ($post) {
            $model->load($post);
            if (empty($model->parent_id)) {
                $model->save();
            } else {
                $model->setScenario('child');
                if ($model->save()) {
                    //推荐问题
                    if (!empty($post['ProductCategory']['question'])) {
                        foreach ($post['ProductCategory']['question'] as $key=>$val) {
                            if (!empty(trim($post['ProductCategory']['question'][$key]))) {
                                $question = new AskQuestion();
                                $question->category_id = $model->id;
                                $question->question = trim($val);
                                $question->order = ($key+1);
                                $question->add_time = time();
                                $question->save();
                            }
                        }
                    }
                } else {
                    return $this->render('create', [
                       'model' => $model,
                       'parentArr' => $parentArr
                   ]);
                }
            }                       
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
                'parentArr' => $parentArr
            ]);
        }
    }

    /**
     * Updates an existing ProductCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        empty($model->parent_id) ? false : $model->setScenario('child');
        //一级分类数组
        $parentArr = CommonFun::getKeyValArr($model, 'id', 'cate_name','parent_id = 0');
        $parentArr = ['0'=> '请选择（不选择则默认创建为一级分类哦）'] + $parentArr;
        $post = Yii::$app->request->post();
        if ($post) {
//             $model->load($post);
            if (empty($model->parent_id)) {
                $model->budget = '';
                $model->cate_h5_img = '';
                $model->cate_app_img = '';
                
                //删除分类问题
                AskQuestion::deleteAll(['category_id' => $id]);
                
                $model->save();
            } else {
//                 $model->setScenario('child');
                if (!empty($post['ProductCategory']['question'])) {
                    $i = 0;
                    foreach ($post['ProductCategory']['question'] as $key=>$val) {
                        $i++;
                        if (!empty(trim($val))) {    
                            $question = AskQuestion::find()->where("{{%ask_question}}.order = $i AND category_id = $id")->one();
                            if ($question) {
                                $question->question = trim($post['ProductCategory']['question'][$i-1]);
                                $question->save();
                            } else {
                                $question = new AskQuestion();
                                $question->category_id = $model->id;
                                $question->question = trim($val);
                                $question->order = ($key+1);
                                $question->add_time = time();
                                $question->save();
                            }
                        } else {
                            $question = AskQuestion::find()->where("{{%ask_question}}.order = $i AND category_id = $id")->one();
                            if (!empty($question)) {
                                $question->delete();
                            }
                        }
                    }                                                
                }
                
                $model->save();
            }

            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
                'parentArr' => $parentArr
            ]);
        }
    }

    /**
     * Deletes an existing ProductCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        //删除分类问题
        AskQuestion::deleteAll(['category_id' => $id]);

        return $this->redirect(['index']);
    }

    /**
     * Finds the ProductCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductCategory::findOne($id)) !== null) {
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

        if($model = ProductCategory::findOne($id)){
            $status = $status == 1 ? 0 : 1;
            $model->$type = $status;
            $model->save(false);
            //分类上下架，产品上下架
//             if ($type == 'status') {
//                 ProductDetails::updateAll(['status'=> $model->status],"cate_id = $id");
//             }

            $data['status'] = "1";
        }

        echo json_encode($data);
    }
}
