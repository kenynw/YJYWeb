<?php

namespace backend\controllers;

use Yii;
use common\models\ReportApp;
use common\models\ReportAppSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ReportAppController implements the CRUD actions for ReportApp model.
 */
class ReportAppController extends Controller
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
     * Lists all ReportApp models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ReportAppSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        $time = date("Y-m-d");
        $start_at = Yii::$app->request->get('start_at') ? Yii::$app->request->get('start_at') : date("Y-m-d",strtotime("$time -7 day"));
        $end_at = Yii::$app->request->get('end_at') ? Yii::$app->request->get('end_at') : $time;
        $referer = isset($_GET['ReportAppSearch']['referer']) ? $_GET['ReportAppSearch']['referer'] : 0;
        
        //统计数据处理
        //处理日期
        $num = (strtotime($end_at) - strtotime($start_at))/(24*3600);
        //$num = $num < 32 ? $num : 31;
        $xline = "";
        $day_list = array();
        for($i=0;$i<=$num;$i++){
            $xline .= "'".date("Y-m-d",strtotime("$start_at +$i day"))."',";
            $day_list[] = strtotime("$start_at +$i day");
        }
        
        $whereStr = " 1=1";
        if($referer){
            $whereStr .= " AND referer = '$referer'";
        }
        
        $list = array();
        if($day_list){
            foreach($day_list as $val){
                $sql = "SELECT FROM_UNIXTIME('$val', '%Y-%m-%d') days,IFNULL(SUM(register_num),0) AS register_num,IFNULL(SUM(banner_click),0) AS banner_click,IFNULL(SUM(banner_click_num),0) AS banner_click_num,IFNULL(SUM(lessons_num),0) AS lessons_num,
                IFNULL(SUM(evaluating_num),0) AS evaluating_num,IFNULL(SUM(article_num),0) AS article_num,IFNULL(SUM(product_num),0) AS product_num
                FROM  {{%report_app}} WHERE $whereStr AND date = '$val'";
                $list[] = Yii::$app->db->createCommand($sql)->queryOne();
            }
        }

        $register_num = "";
        $banner_click = "";
        $banner_click_num = "";
        $lessons_num = "";
        $evaluating_num = "";
        $article_num = "";
        $product_num = "";
        if($list){
            foreach($list as $val){
                $register_num .= $val['register_num'].",";
                $banner_click .= $val['banner_click'].",";
                $banner_click_num .= $val['banner_click_num'].",";
                $lessons_num .= $val['lessons_num'].",";
                $evaluating_num .= $val['evaluating_num'].",";
                $article_num .= $val['article_num'].",";
                $product_num .= $val['product_num'].",";
            }
        }

        return $this->render('index', [
            'xline' => trim($xline,","),
            'register_num' => trim($register_num,","),
            'banner_click' => trim($banner_click,","),
            'banner_click_num' => trim($banner_click_num,","),
            'lessons_num' => trim($lessons_num,","),
            'evaluating_num' => trim($evaluating_num,","),
            'article_num' => trim($article_num,","),
            'product_num' => trim($product_num,","),
            
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ReportApp model.
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
     * Creates a new ReportApp model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ReportApp();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing ReportApp model.
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
     * Deletes an existing ReportApp model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the ReportApp model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ReportApp the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ReportApp::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
