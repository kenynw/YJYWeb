<?php

namespace backend\controllers;

use Yii;
use common\models\HuodongSpecialDraw;
use common\models\HuodongSpecialDrawSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\HuodongAddress;
use common\models\HuodongDrawLog;

/**
 * HuodongSpecialDrawController implements the CRUD actions for HuodongSpecialDraw model.
 */
class HuodongSpecialDrawController extends Controller
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
     * Lists all HuodongSpecialDraw models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new HuodongSpecialDrawSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single HuodongSpecialDraw model.
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
     * Creates a new HuodongSpecialDraw model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new HuodongSpecialDraw();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing HuodongSpecialDraw model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
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
     * Deletes an existing HuodongSpecialDraw model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $uid = $model->uid;
        $hid = $model->hdid;
        //获奖
        $this->findModel($id)->delete();
        try {
            //地址
            HuodongAddress::deleteAll(['user_id'=>$uid,'hid'=>$hid]);

            //助攻
            HuodongDrawLog::deleteAll(['relation_id'=>$id,'hid'=>$hid]);
            return $this->redirect(['index']);
        } catch (Exception $e) {
            throw new NotFoundHttpException("error");
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the HuodongSpecialDraw model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return HuodongSpecialDraw the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HuodongSpecialDraw::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    //修改发放状态
    public function actionUpdateSendstatus($id)
    {
        $model = $this->findModel($id);
        if (Yii::$app->request->isAjax && Yii::$app->request->post()) {
            $send = Yii::$app->request->post('send');
            if ($model->sendstatus == $send) {
                if ($model->sendstatus == 0) {
                    $model->sendstatus = 1;
                } else {
                    $model->sendstatus = 0;
                }
                if ($model->save()) {
                    $data = ['status' => '1', 'sendstatus' => $model->sendstatus];
                } else {
                    $data = ['status' => '0'];
                }
            } else {
                $data = ['status' => '0'];
            }
            return json_encode($data);
        }
    
        return $this->redirect(['index']);
    }
    
    //改变中奖状态
    public function actionChangeStatus()
    {
        $id = Yii::$app->request->post("id");
        $status = Yii::$app->request->post("status");
        $type = Yii::$app->request->post("type");
    
        $data['status'] = "0";
    
        if($model = $this->findModel($id)){
            $status = $status == 1 ? 2 : 1;
    
            $model->$type = $status;
            $model->save(false);
    
            $data['status'] = "1";
        }
    
        echo json_encode($data);
    }
    
    //导出excel
    public function actionExport()
    {
        set_time_limit(0);
        ini_set('memory_limit','256M');
        require Yii::getAlias('@common').'/extensions/PHPExcel/Classes/PHPExcel.php';
        $PHPExcel = new \PHPExcel();
    
        if($_POST){
            if ($_POST['hdid']) {
                $hdid = $_POST['hdid'];
                $data= HuodongAddress::find()->where("hid = $hdid")->groupBy('user_id')->asArray()->all();   //查出数据
                $name='名单';    //生成的Excel文件文件名
                $objPHPExcel = new \PHPExcel();  

                if (!empty($data)) {
                    foreach($data as $k => $v){        
                    $num=$k+1;  
                    $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValue('A'.$num, $v['name'])     
                                ->setCellValue('B'.$num, $v['tel'])  
                                ->setCellValue('C'.$num, $v['address']);
                    }  
      
                    $objPHPExcel->getActiveSheet()->setTitle('User');  
                    $objPHPExcel->setActiveSheetIndex(0);  
                    header('Content-Type: applicationnd.ms-excel');  
                    header('Content-Disposition: attachment;filename="'.$name.'.xls"');  
                    header('Cache-Control: max-age=0');  
                    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
                    $objWriter->save('php://output');  
                    exit;  
                } else {
                    Yii::$app->getSession()->setFlash('error', '无数据，导出失败');
                    return $this->redirect(['index']);
                }
            }   
        }
    }
}
