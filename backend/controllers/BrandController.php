<?php

namespace backend\controllers;

use Yii;
use common\models\Brand;
use common\models\BrandCategory;
use common\models\BrandSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\ProductDetails;
use common\functions\Functions;
use common\models\AdminLogSearch;
use yii\base\Object;
use backend\models\CommonFun;

/**
 * BrandController implements the CRUD actions for Brand model.
 */
class BrandController extends Controller
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
                    //'imagePathFormat' => "../../frontend/web/uploads/product_img/{yyyy}{mm}{dd}/{time}{rand:6}",//上传图片的路径
                    'imagePathFormat' => $path."brand_img/{yyyy}{mm}{dd}/{time}{rand:6}",
                    // 'imagePathFormat' => "/uploads/banner/{time}{rand:6}",
                ]
            ]
        ];
    }

    /**
     * Lists all Brand models.
     * @return mixed
     */
    public function actionIndex()
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
            $filepath = Yii::$app->basePath."/web";
            $arr=explode(".", $_FILES["file"]["name"]);
            $hz=strtolower($arr[count($arr)-1]);

            if(!is_dir($filepath)) { mkdir($filepath, 0777); chmod($filepath, 0777);}
            $randname = date('Ymd',time()).rand(1000, 9999).".".$hz;
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
                                $check = Brand::find()->where(['name'=>$list[0]])->one();
                            }

                            if(!$check && (!empty($list[0]) || !empty($list[1])) ){
                                $cate_id = BrandCategory::find()->select("id")->where(['name' => $list[4]])->scalar();

                                //一行行的插入数据库操作
                                $model = new Brand;
                                $model->name = addslashes($list[0]);
                                $model->ename = addslashes($list[1]);
                                $model->alias = addslashes($list[2]);
                                $model->hot = $list[3];
                                $model->cate_id = $cate_id ? $cate_id : 0;
                                $model->description = addslashes($list[5]);
                                $model->created_at  = time();
                                $model->letter = empty($model->name) ? '' : Functions::getFirstCharter($model->name);                                
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
        }else{
            $searchModel = new BrandSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            
            //产品总数
            $productNum = Brand::find()
            ->select("pd.product_num")
            ->leftJoin("(SELECT count(id) as product_num,brand_id FROM {{%product_details}} GROUP BY brand_id) as pd","pd.brand_id = {{%brand}}.id")
            ->asArray()
            ->column();
            $productNum = array_sum($productNum);

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'productNum' => $productNum
            ]);
        }

    }

    /**
     * Displays a single Brand model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $model->parent_id = empty(Brand::findOne($model->parent_id)) ? '无' : Brand::findOne($model->parent_id)->name;
        
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Brand model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Brand();
        $post = Yii::$app->request->post();
        
        if ($post) {
            $post['Brand']['letter'] = empty(Functions::getFirstCharter($post['Brand']['ename'])) ? Functions::getFirstCharter($post['Brand']['name']) : Functions::getFirstCharter($post['Brand']['ename']);
            
            //推荐时间
            if ($model->is_recommend == '1') {
                $model->retime = time();
            };
            //二级批号规则与一级一致
            if (!empty($post['Brand']['parent_id'])) {
                $rule = Brand::findOne($post['Brand']['parent_id'])->rule;
                $model->rule = $rule;
            }
            if ($model->load($post) && $model->save()) {
                //添加品牌操作记录
                CommonFun::addAdminLogView('创建品牌',$model->id);
                
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('create', [
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
     * Updates an existing Brand model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = Yii::$app->request->post();

        if ($post) {

            $post['Brand']['letter'] = empty(Functions::getFirstCharter($post['Brand']['ename'])) ? Functions::getFirstCharter($post['Brand']['name']) : Functions::getFirstCharter($post['Brand']['ename']);

            //推荐时间
            if ($post['Brand']['is_recommend'] == '1') {
                if ($model->is_recommend == '0') $post['Brand']['retime'] = time();
            } else {
                $post['Brand']['retime'] = '0';
            }
            //二级批号规则与一级一致           
            if (!empty($post['Brand']['parent_id'])) {
                $rule = Brand::findOne($post['Brand']['parent_id'])->rule;
                $post['Brand']['rule'] = $rule;
            }
            $model->load($post);
            $model->save();
            
            //添加品牌操作记录
            CommonFun::addAdminLogView('编辑品牌',$model->id);
            
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Brand model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        //修改与此品牌关联的产品
        ProductDetails::updateAll(['brand_id'=> '0','has_brand' => '0'],"brand_id = $id");

        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
    }

    /**
     * Finds the Brand model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Brand the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Brand::findOne($id)) !== null) {
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
    
        if($model = Brand::findOne($id)){
            $status = $status == 1 ? 0 : 1;
            
            //推荐时间
            if($type == "is_recommend"){
                $model->retime = $status == 1 ? time() : 0;
            }
            
            $model->$type = $status;
            $model->save(false);    
            $data['status'] = "1";
        }
    
        echo json_encode($data);
    }

//    public function actionTest(){
//        $sql = "SELECT id,img FROM {{%brand}}";
//        $brand_list  = Yii::$app->db->createCommand($sql)->queryAll();
//
//        foreach($brand_list as $val){
//            if(!empty($val['img'])){
//
//                $list = explode("/",$val['img']);
//                if(count($list) == 2){
//                    $ext = explode(".",$val['img']);
//                    $ext = $ext[1];
//                    $image = rand(1,10000) . date("Ymd") . $val['id'] . '.' .  $ext;
//
//                    //先上传图片
//                    $url = @file_get_contents( Yii::$app->params['uploadsUrl'] . $val['img']);
//                    $filename = Yii::$app->basePath . "/web/uploads/" . $image;
//                    file_put_contents($filename, $url);
//
//                    if(file_exists($filename)) {
//                        //上传到OSS
//                        $fullname = Yii::$app->params['environment'] == "Development" ? "cs/uploads/" : "uploads/";
//                        $path = "brand_img/" . date("Ymd") ."/" . $image;
//                        $upload = new OssUpload();
//                        $upload->upload($filename,$fullname.$path);
//
//                        unlink($filename);
//                    }
//
//                    $sql  = "UPDATE {{%brand}} SET img = '$path' WHERE id = '{$val['id']}'";
//                    $return = Yii::$app->db->createCommand($sql)->execute();
//
//                    echo "<pre>";
//                    print_r($brand_list);
//
//                }
//            }
//        }
//
//    }





}
