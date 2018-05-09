<?php

namespace backend\controllers;

use Yii;
use common\models\Ranking;
use common\models\RankingSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\functions\Functions;
use yii\sphinx\Query;
use common\models\RankingList;
use yii\sphinx\MatchExpression;
use yii\base\Object;
use backend\models\CommonFun;

/**
 * RankingController implements the CRUD actions for Ranking model.
 */
class RankingController extends Controller
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
                    'imagePathFormat' => $path."rank_banner/{yyyy}{mm}{dd}/{time}{rand:6}",
                ]
            ]
        ];
    }

    /**
     * Lists all Ranking models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new RankingSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        //分类
        $cateList = CommonFun::getCateList($where = 'parent_id <> 0');

        return $this->render('index', [
            'cateList' => $cateList,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Ranking model.
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
     * Creates a new Ranking model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Ranking();

        $post = Yii::$app->request->post();
        //每个分类创建榜单不超过5个
//         if ($post) {
//             $cateCount = Ranking::find()->where("category_id = {$post['Ranking']['category_id']}")->count();
//             if ($cateCount == '5') {
//                 Yii::$app->getSession()->setFlash('error', '添加失败！每个分类的榜单设置不能超过5个哦');
//                 return $this->redirect(['index']);
//             }
//         }
        if ($model->load($post) && $model->save()) {           
            //分类榜单产品
            $id = $model->id;
            if (!empty($post['Ranking']['product'])) {
                $productArr = explode(',', $post['Ranking']['product']);
                foreach ($productArr as $key=>$val) {
                    $ranklist = new RankingList();
                    $ranklist->product_id = $val;
                    $ranklist->ranking_id = $id;
                    $ranklist->order = $key+1;
                    $ranklist->save();       
                }
            }

            return $this->redirect(['index']);
        } else {
            //分类
            $cateList = CommonFun::getCateList($where = 'parent_id <> 0');            
            return $this->render('_form', [
                'cateList' => $cateList,
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Ranking model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);        
        
        //原已经添加的榜单产品
        $productIdArr =  RankingList::find()->select('product_id')->where("ranking_id = $id")->orderBy('order')->asArray()->column();
        $productList =  RankingList::find()
            ->select('product_id,p.id,p.product_name')
            ->where("ranking_id = $id")
            ->orderBy('order')
            ->joinWith('productDetails p')
            ->asArray()
            ->all();
        //原榜单产品数
        $uplen = count($productIdArr) + 1;
        
        $post = Yii::$app->request->post();
        //每个分类创建榜单不超过5个
//         if ($post) {
//             $cateCount = Ranking::find()->where("category_id = {$post['Ranking']['category_id']}")->count();
//             //修改自属5个分类的榜单
//             $rankIdArr = Ranking::find()->select('id')->where("category_id = {$post['Ranking']['category_id']}")->asArray()->column();
//             if ($cateCount >= '5' && !in_array($id, $rankIdArr)) {
//                 Yii::$app->getSession()->setFlash('error', '修改失败！每个分类的榜单设置不能超过5个哦');
//             }
//         }

        //分类榜单产品
        if (!empty($post['Ranking']['product'])) {
            $productArr = explode(',', $post['Ranking']['product']);
            foreach ($productArr as $key=>$val) {
                //存现存的数据
                $nowexist[] = $val;
        
                $rankList = RankingList::find()->where("ranking_id = $id AND product_id = $val")->all();
                if (empty($rankList)) {
                    //新增，建立关系
                    $rankListModel = new RankingList();
                    $rankListModel->product_id = $val;
                    $rankListModel->ranking_id = $id;
                    $rankListModel->order = $key+1;
                    $rankListModel->save();
                } else {
                    //已存在，修改顺序
                    $rankListModel = RankingList::find()->where("ranking_id = $id AND product_id = $val")->one();
                    $rankListModel->order = $key+1;
                    $rankListModel->save();
                }
            }
        
            //如果有删减过已建立关系的标签，则删除已建立的关系
            $delrankList = array_diff($productIdArr,$nowexist);
            if (!empty($delrankList)) {
                foreach ($delrankList as $key=>$val) {
                    $ranklist = RankingList::find()->where("product_id = $val AND ranking_id = $id")->one();
                    $ranklist->delete();
                }
            }

            //后已经添加的榜单产品
            $productList =  RankingList::find()
                ->select('product_id,p.id,p.product_name')
                ->where("ranking_id = $id")
                ->orderBy('order')
                ->joinWith('productDetails p')
                ->asArray()
                ->all();
        }

        if ($model->load($post) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            //分类
            $cateList = CommonFun::getCateList($where = 'parent_id <> 0');            
            return $this->render('_form', [
                'model' => $model,
                'cateList' => $cateList,
                'productList' => $productList,
                'uplen' => $uplen
            ]);
        }
    }

    /**
     * Deletes an existing Ranking model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        //删除关系
        RankingList::deleteAll(['ranking_id' => $id]);

        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
    }

    /**
     * Finds the Ranking model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Ranking the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ranking::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    //搜索产品
    public function actionSearchProduct ($q=""){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $out = ['results' => []];
        if (!$q) {
            return $out;
        }
    
        $idStr      =   '';
        $brandId    =   '';
        $idArr      =   [];
        $search     =   Functions::checkStr($q);
        $sphinx_query      = new Query();
        //先匹配品牌
        $brandRows  = $sphinx_query->select('id')->from('brand')->match(
            (new MatchExpression())
                ->match(['name' => $search])
                ->orMatch(['ename' => $search])
                ->orMatch(['alias' => $search])
            )->one();
        if($brandRows) $brandId = $brandRows['id'];
        $rows       = $sphinx_query->select('id')->from('product')->match($search)->limit(1000)->all();
        
        if(!$rows && !$brandId) {
            $data = (new \yii\db\Query())
            ->select('id as id, product_name as text')
            ->from('{{%product_details}}')
            ->where(['like', 'product_name', $q])
            ->andWhere("status = 1")
            ->limit(50)
            ->all();
        } else {
            foreach ($rows as $key => $value) {
                $idArr[] = $value['id'];
            }
            $idStr = !empty($idArr) ?  Functions::db_create_in($idArr,'id') : ' (1 = 1) ' ;
            $idStr = $brandId ?  "(brand_id = '$brandId'  OR " . $idStr.")" : $idStr ;
            $data = (new \yii\db\Query())
            ->select('id as id, product_name as text')
            ->from('{{%product_details}}')
            ->where("$idStr")
            ->andWhere("$idStr")
            ->andWhere("status = 1")
            ->limit(50)
            ->all();
        }

        $out['results'] = array_values($data);
    
        return $out;
    }
    
    //改变状态
    public function actionChangeStatus()
    {
        $id = Yii::$app->request->post("id");
        $status = Yii::$app->request->post("status");
        $type = Yii::$app->request->post("type");
    
        $data['status'] = "0";
    
        if($model = Ranking::findOne($id)){
            $status = $status == 1 ? 0 : 1;
    
            $model->$type = $status;
            $model->save(false);
            $data['status'] = "1";
        }
    
        echo json_encode($data);
    }
    
    //批量上架
    public function actionBottomUpdate(){
        $ids = Yii::$app->request->post('id');
        $type = Yii::$app->request->post('type');
        $type_id = Yii::$app->request->post('type_id');
    
        foreach ($ids as $key=>$val) {
            $model= $this->findModel($val);
                $model->$type = $type_id;
                $model->save(false);
        }

        $data['status'] = "1";
    
        echo json_encode($data);
    }
}
