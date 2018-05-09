<?php

namespace backend\controllers;

use Yii;
use common\models\ProductBonus;
use common\models\ProductBonusSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\base\Object;

/**
 * ProductBonusController implements the CRUD actions for ProductBonus model.
 */
class ProductBonusController extends Controller
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
     * Lists all ProductBonus models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductBonusSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ProductBonus model.
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
     * Creates a new ProductBonus model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductBonus();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing ProductBonus model.
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
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing ProductBonus model.
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
    
    //批量删除
    public function actionDeleteAll(){
        if($_POST['id']){
            foreach($_POST['id'] as $val){
                $this->findModel($val)->delete();
            }
            echo 1;
        }
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

    /**
     * Finds the ProductBonus model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ProductBonus the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductBonus::findOne($id)) !== null) {
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
    
        if($model = $this->findModel($id)){
            $status = $status == 1 ? 0 : 1;
    
            $model->$type = $status;
            $model->save(false);
    
            $data['status'] = "1";
        }
    
        echo json_encode($data);
    }
    
    public function actionValidateForm ($id = '') {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if ($id) {
            $model = ProductBonus::findOne($id);
        } else {
            $model = new ProductBonus();
        }
        $model->load(Yii::$app->request->post());
        return \yii\widgets\ActiveForm::validate($model);
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
            $filepath = Yii::$app->basePath."/web/uploads/product_bonus";
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
                    $arr[0] = '';
                    $arr[1] = '';
                    $repeatIds = '';
    
                    if($total_line > 1){
                        for($row = 2;$row <= $total_line; $row++){
                            $list = array();
                            for($column = 'A'; $column <= $total_column; $column++){
                                $list[] = trim($phpexcel->getCell($column.$row)->getValue());
                            }
    
                            $check = 0;
    
                            if($list[1]){
                                $check = ProductBonus::find()->where(['goods_id'=>$list[1]])->one();
                                
                                //记录重复数据
                                if ($check) {
                                    $repeatIds .= $list[1].',';
                                }
                            }
                            if(!$check && (!empty($list[1]) || !empty($list[2])) ){
                                if (!preg_match('/https:\/\/s.click.taobao.com/', $list[2], $matches)) {
                                    $arr[0] .= $list[1].',';
                                } elseif (!empty($list[3]) && !preg_match('/https:\/\/uland.taobao.com/', $list[3], $matches)) {
                                    $arr[1] .= $list[1].',';
                                } else {
                                    //一行行的插入数据库操作
                                    $model = new ProductBonus();
                                    $model->sort = empty($list[0]) ? '0' : $list[0];
                                    $model->goods_id = $list[1];
                                    $model->goods_link = $list[2];
                                    $model->bonus_link = $list[3];
                                    $model->price = $list[4];
                                    $model->status = '1';
                                    $model->start_date = str_replace('.', '-', $list[5]);
                                    $model->end_date = str_replace('.', '-', $list[6]);
                                    if($model->save(false)){
                                        $i++;
                                    }
                                }
                            }
    
                        }
                        //删除文件
                        @unlink ($excelFile);
                    }
    
                    $msg = '共'. ($total_line - 1) .'条数据，成功导入' . $i . "数据。";
    
                    if (!empty($arr[0]) || !empty($arr[1]) || !empty($repeatIds)) {
                        if (!empty($arr[0])) {
                            $msg .= '失败的商品id有(商品链接不正确)：'.$arr[0];
                        } 
                        if (!empty($arr[1])) {
                            $msg .= '失败的商品id有(优惠券链接不正确)：'.$arr[1];
                        }  
                        if (!empty($repeatIds)) {
                            $msg .= '重复的商品id有：'.$repeatIds; 
                        } 
                        Yii::$app->getSession()->setFlash('error', $msg);
                    } else {
                        Yii::$app->getSession()->setFlash('success', $msg);
                    }

                    return $this->redirect(['index']);
                }
            }
        }
    }
}
