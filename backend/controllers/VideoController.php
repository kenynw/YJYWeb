<?php

namespace backend\controllers;

use Yii;
use common\models\Video;
use common\models\VideoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\functions\Functions;
use yii\web\UploadedFile;
use yii\base\Object;
use common\models\CommentSearch;
use common\components\Youtube;

/**
 * VideoController implements the CRUD actions for Video model.
 */
class VideoController extends Controller
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
                    'imagePathFormat' => $path."videos/{yyyy}{mm}{dd}/{time}{rand:6}",
                ]
            ],
        ];
    }

    /**
     * Lists all Video models.
     * @return mixed
     */
    public function actionIndex1()
    {
        $searchModel = new VideoSearch();
        $params = Yii::$app->request->queryParams;
        $params['VideoSearch']['type'] = '1';
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'type' => '1',
        ]);
    }
    
    public function actionIndex2()
    {
        $searchModel = new VideoSearch();
        $params = Yii::$app->request->queryParams;
        $params['VideoSearch']['type'] = '2';
        $dataProvider = $searchModel->search($params);                
    
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'type' => '2',
        ]);
    }

    /**
     * Displays a single Video model.
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
     * Creates a new Video model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Video();
        
        $post = Yii::$app->request->post();
        if ($post) {       
//             $file = UploadedFile::getInstance($model, 'file');        
//             $path = Yii::$app->basePath. "/../frontend/web/";
//             $filePath =  Yii::$app->basePath. "/../frontend/web/videos/";
            
//             if ($file) {
//                 $filename = "videos/" . time() . rand(1,1000) .  '.' . $file->getExtension();
//                 $path = $path . $filename;
                
//                 if(!file_exists($filePath)){
//                     mkdir($filePath);
//                 }
//                 $file->saveAs($path);
                $url = $path = Yii::$app->params['uploadsUrl'].$post['Video']['video'];
                $header_array = get_headers($url, true);
                $size = $header_array['Content-Length'];                
                
                $post['Video']['video'] = $post['Video']['video'];
                $post['Video']['filesize'] = $size;
                $post['Video']['ext'] = explode('.', basename($post['Video']['video']))[1];
                $post['Video']['is_complete'] = '1';
                $post['Video']['product_id'] = empty($post['Video']['product_id']) ? '' : join(',', $post['Video']['product_id']);
//             }

            if ($model->load($post) && $model->save()) {
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
    
    public function actionCreate2()
    {
        $post = Yii::$app->request->post();
        
        if ($post) {
            $url = $post['Video']['url'];
            $youtube   = new Youtube();
            $return    = $youtube->uploadFile($url);
            
            if ($return['status'] != 1) {
                Yii::$app->getSession()->setFlash('error', $return['msg']);
            } else {
                Yii::$app->getSession()->setFlash('success', $return['msg']);
            }
            
            return $this->redirect(['index2']);
        } else {
            return $this->renderAjax('create2', [
            
            ]);
        }
    }

    /**
     * Updates an existing Video model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        $post = Yii::$app->request->post();
        if ($post) {
//             $file = UploadedFile::getInstance($model, 'file');
//             $path = Yii::$app->basePath. "/../frontend/web/";
//             $filePath =  Yii::$app->basePath. "/../frontend/web/videos/";
            
//             if ($file) {
//                 $filename = "videos/" . time() . rand(1,1000) .  '.' . $file->getExtension();
//                 $path = $path . $filename;
                
//                 if(!file_exists($filePath)){
//                     mkdir($filePath);
//                 }
//                 $file->saveAs($path);
                
                $url = $path = Yii::$app->params['uploadsUrl'].$post['Video']['video'];
                $header_array = get_headers($url, true);
                $size = $header_array['Content-Length'];                
                
                $post['Video']['video'] = $post['Video']['video'];
                $post['Video']['filesize'] = $size;
                $post['Video']['ext'] = explode('.', basename($post['Video']['video']))[1];
                $post['Video']['is_complete'] = '1';
                $post['Video']['product_id'] = empty($post['Video']['product_id']) ? '' : join(',', $post['Video']['product_id']);
//             }
            
            if ($model->load($post) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Video model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id,$type)
    {
        $this->findModel($id)->delete();

        return $this->redirect(["index$type"]);
    }

    /**
     * Finds the Video model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Video the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Video::findOne($id)) !== null) {
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
    
    //下载
    public function actionDownload($id,$type)
    {
        $model = $this->findModel($id);
        if ($model) {
            if ($type == '1') {
                //网络地址
                $path = Yii::$app->params['uploadsUrl'].$model->video;
            } else {
//                 $path = Yii::$app->params['frontendUrl'].$model->video;
                //本地路径
                $path = dirname(yii::$app->basePath)."/frontend/web/".$model->video;
            }
        }

        $fileName = basename($path); 
        urldecode($fileName);
        header("Content-Type: application/force-download;");
        header("Content-Disposition: attachment; filename=$fileName");
        
        if ($type == '1') {
            readfile($path);
        } else {
            //防止服务器瞬时压力增大，分段读取
            $buffer   =10240;
            $fp=fopen($path,"r+");//下载文件必须先要将文件打开，写入内存
            while(!feof($fp)){
                $file_data=fread($fp,$buffer);
                echo $file_data;
            }
            //关闭文件
            fclose($fp);
        }
    }
}
