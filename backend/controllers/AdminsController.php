<?php

namespace backend\controllers;

use Yii;
use common\models\Admin;
use common\models\AdminSearch;
use common\models\AdminCreate;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\User;

class AdminsController extends Controller
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

    public function actionIndex()
    {
        $searchModel = new AdminSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->renderAjax('view', [
            'model' => $this->findModel(Admin::className(),$id),
        ]);
    }

    public function actionCreate()
    {
        $model = new AdminCreate();

        if ($model->load(Yii::$app->request->post()) && $model->saveData()) {
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }
    
    public function actionStatus($id){
        $model = $this->findModel(Admin::className(),$id);
        if($model->status){
            $model->status = User::STATUS_DELETED;
        }else{
            $model->status = User::STATUS_ACTIVE;
        }
        
        $model->save();
        
        Yii::info($model->getErrors());
        
        return $this->redirect(['index']);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel(AdminCreate::className(),$id);
        if ($model->load(Yii::$app->request->post()) && $model->saveData()) {
            return $this->redirect(['index']);
        } else {
            if(isset($_GET['type']) && $_GET['type'] == "ajax") {
                return $this->renderAjax('update', [
                    'model' => $model,
                ]);
            }else{
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }
    }

    public function actionDelete($id)
    {
        $this->findModel(Admin::className(),$id)->delete();
        
        $sql = "delete from {{%auth_assignment}} WHERE user_id = '$id'";
        Yii::$app->db->createCommand($sql)->execute();

        return $this->redirect(['index']);
    }

    protected function findModel($form,$id)
    {
        if (($model = $form::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionAdminUpdate($adminId)
    {
        $model = \backend\models\User::findOne($adminId);
        if ($model) {
            $data = '';
            $userList = [];
            $users = \common\models\User::find()->where('id = admin_id')->all();
            foreach ($users as $key => $value) {
                $userList[$value['id']] = $value['username'];
            }
            if (Yii::$app->request->post()) {
                $data = Yii::$app->request->post();
                $data['User']['connect_user_username'] = \common\models\User::getUsername($data['User']['connect_user_id']);
            }

            if ($model->load($data) && $model->save()) {
                return $this->redirect(['index']);
            } else {
                return $this->render('adminUpdate', [
                    'model' => $model,
                    'userList' => $userList,
                ]);
            }
        } else {
            throw new NotFoundHttpException('用户不存在');
        }
    }

    public function actionCheckName($id,$name){

        if($id){
            $cond = [ 'and',['!=', 'id', $id], 'username = "$name"'];
        }else{
            $cond = [ 'username' => $name];
        }

        $model = Admin::find()->where($cond)->one();
        if($model){
            echo 1;
        }
    }

}
