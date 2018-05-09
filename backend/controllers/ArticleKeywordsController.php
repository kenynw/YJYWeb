<?php

namespace backend\controllers;

use Yii;
use common\models\ArticleKeywords;
use common\models\ArticleKeywordsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\functions\Tools;
use common\models\Article;
use yii\base\Object;

/**
 * ArticleKeywordsController implements the CRUD actions for ArticleKeywords model.
 */
class ArticleKeywordsController extends Controller
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
     * Lists all ArticleKeywords models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ArticleKeywordsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ArticleKeywords model.
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
     * Creates a new ArticleKeywords model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ArticleKeywords();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('_form', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing ArticleKeywords model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('_form', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing ArticleKeywords model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the ArticleKeywords model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ArticleKeywords the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ArticleKeywords::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    //更新内链
    public function actionUpdateLink()
    {
        $tools = new Tools();
        $count = Article::find()->count();
        $pageSize = 10;
        $pageMin = 0;
        $total = intval(ceil($count/$pageSize));
        $rec = [];
    
        for($i=1;$i<=$total;$i++) {
    
            $sql = "SELECT id FROM {{%article}} limit $pageMin,$pageSize";
            $idArr = Yii::$app->db->createCommand($sql)->queryColumn();
    
            if (!empty($idArr)) {
                foreach ($idArr as $key=>$val) {
                    $model = Article::findOne($val);
                    $content = $model->content;
                    $model->content = $tools->keylink($content);
                    $model->save(false);
                }
                $pageMin = $pageMin + 10;
                usleep(100);
            }
        }
    
        Yii::$app->getSession()->setFlash('success', '操作成功');
        return $this->redirect(['index']);
    }
    
    //导入内链
    public function actionImport()
    {
        set_time_limit(0);
        ini_set('memory_limit','256M');
        require Yii::getAlias('@common').'/extensions/PHPExcel/Classes/PHPExcel.php';
        
        $data = array();
        if($_POST){
        
        
        if($_FILES["file"]["error"] > 0){
            $data = array('error'=>'1','msg'=>'文件上传失败,请重新上传..','info'=>'');
        }
    
        $excelFile = '';    //文件名
        $filepath = Yii::$app->basePath."/web/uploads/article_keywords_link";
        $arr=explode(".", $_FILES["file"]["name"]);
        $hz=strtolower($arr[count($arr)-1]);
    
        if(!is_dir($filepath)) { mkdir($filepath, 0777); chmod($filepath, 0777);}
        $randname = date('YmdHi',time()).rand(1000, 9999).".".$hz;
        if(is_uploaded_file($_FILES["file"]["tmp_name"])){      //将临时位置的文件移动到指定的目录上即可
            if(move_uploaded_file($_FILES["file"]["tmp_name"], $filepath.'/'.$randname)){
                $excelFile = $filepath.'/'.$randname;       //上传成功的节奏
                chmod($excelFile, 0777);
            }
        }
        if(!$excelFile){        //文件不存在
            $data = array('error'=>'2','msg'=>'文件上传失败,请重新上传,检查文件名..','info'=>'');
        }else{      //读取Excel
    
            if(in_array($hz,array('xls','xlsx'))){
                $phpexcel=new \PHPExcel();
    
                if ($hz == "xls") {
                    $excelReader = \PHPExcel_IOFactory::createReader('Excel5');
                } else {
                    $excelReader = \PHPExcel_IOFactory::createReader('Excel2007');
                }
    
                $phpexcel    = $excelReader->load($excelFile)->getSheet(0);//载入文件并获取第一个sheet
                $total_line  = $phpexcel->getHighestRow();//总行数
                $total_column= $phpexcel->getHighestColumn();//总列数
    
                $i = 0;
                
                if($total_line > 1){
                    for($row = 2;$row <= $total_line; $row++){
                        $list = array();
                        for($column = 'A'; $column <= $total_column; $column++){
                            $list[] = trim($phpexcel->getCell($column.$row)->getValue());
                        }
    
                        $check = 0;

                        if($list[0]){
                            $check = ArticleKeywords::find()->where(['keyword'=>$list[0]])->one();
                        }
                        if(!$check && (!empty($list[0]) || !empty($list[1])) ){    
                            //一行行的插入数据库操作
                            $model = new ArticleKeywords();
                            $model->keyword = addslashes($list[0]);
                            $model->link = $list[1];                             
                            if($model->save(false)){
                                $i++;
                            }
                        }

                    }
                    //删除文件
                    @unlink ($excelFile);
                }

                $msg = '共'. ($total_line - 1) .'条数据，成功导入' . $i . "数据。";

                Yii::$app->getSession()->setFlash('success', $msg);
                return $this->redirect(['index']);
                }
            }
        }
    }
    
    public function actionValidateForm () {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new ArticleKeywords();
        $model->load(Yii::$app->request->post());
        return \yii\widgets\ActiveForm::validate($model);
    }
}
