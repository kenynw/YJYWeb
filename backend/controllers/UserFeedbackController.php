<?php

namespace backend\controllers;

use Yii;
use common\models\UserFeedback;
use common\models\UserFeedbackSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\base\Object;
use common\functions\ReplyFunctions;
use common\functions\NoticeFunctions;
use common\models\Pms;
use common\functions\Functions;
use common\models\User;

/**
 * UserFeedbackController implements the CRUD actions for UserFeedback model.
 */
class UserFeedbackController extends Controller
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
                    'imagePathFormat' => $path."user_feedback/{yyyy}{mm}{dd}/{time}{rand:6}",
                ]
            ]
        ];
    }

    /**
     * Lists all UserFeedback models.
     * @return mixed
     */
    public function actionIndex()
    {
        //更新状态
        Yii::$app->db->createCommand()->update('{{%user_feedback}}', ['status' => 1])->execute();

        $searchModel = new UserFeedbackSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * Lists all UserFeedback models.
     * @return mixed
     */
    public function actionImageZoom()
    {
        return $this->renderPartial('image_zoom.php');
    }
    /**
     * Displays a single UserFeedback model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        //回复内容
        $pms = Pms::find()->where("type = 4 AND relation_id = $id")->all();
        $record = '';
        if ($pms) {
            foreach ($pms as $key=>$val) {
                //处理图片显示
                preg_match_all('/<img.*?src="(.*?)".*?>/is',$val->message,$array);
                $src = isset($array[1][0]) ? $array[1][0] : '';
                $message = preg_replace('/<br><img.*>/is', '&nbsp;<a class="img" href="javascript:void(0)" data-url="'.$src.'" data-toggle="modal" data-target="#img" target="">查看图片</a>', $val->message);
                
                $record .= "<div style = 'border:1px solid #d2d6de;width:100%;padding:10px;margin-top:20px'>
                      <label class='control-label' for=''>回复人：</label>".$val->admin->username."<br><br>
                      <label class='control-label' for=''>回复时间：</label>".date('Y-m-d H:i:s',$val->created_at)."<br>
                      <br><label class='control-label' for=''>回复内容</label><div style = 'border:1px solid #d2d6de;width:100%;padding:5px;margin-top:2px'>".$message."</div></div>";
            }
        } else {
            $record = '无';
        }
            
        return $this->render('view', [
            'model' => $this->findModel($id),
            'record' => $record
        ]);
    }

    /**
     * Creates a new UserFeedback model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id)
    {
        $model = new UserFeedback();
        $post = Yii::$app->request->post();
        $user_img = User::find()->where("id = $id")->one()['img'];
        if ($post) {
            $model = $this->findModel($id);
            
            //拼接图片
            if (!empty($post['UserFeedback']['picture'])) {
                $post['UserFeedback']['feedback'] = $post['UserFeedback']['feedback'].'<br><img src="'.Functions::get_image_path($post['UserFeedback']['picture']).'">';
            }
            
            //添加通知
            ReplyFunctions::reply(yii::$app->user->identity->id,$id,ReplyFunctions::$USER_FEED_BACK,$post['UserFeedback']['feedback']);
            NoticeFunctions::JPushOne(['Alias' => $model->user_id, 'id' => 0, 'type' => 0, 'option' => 'feedback', 'replaceStr' => $post['UserFeedback']['feedback']]);
            $model->is_feedback = '1';
            $model->save(false);
            
            $url = Yii::$app->request->referrer;
            return $this->redirect($url);
        } else {
            return $this->renderAjax('chat', [
                'model' => $model,
                'user_id'=>$id,
                'user_img'=>Functions::get_image_path($user_img,1,150,150)
            ]);
        }
    }

    /**
     * Updates an existing UserFeedback model.
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
    public function actionSwoole($user_id,$user_img)
    {
      
      return $this->renderPartial('swoole.php',[
          'user_id'=>$user_id,
          'user_img'=>$user_img
        ]);
        
    }

    /**
     * Deletes an existing UserFeedback model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id,$url)
    {
//         $this->findModel($id)->delete();
        //删除当前用户的
        UserFeedback::deleteAll(['user_id' => $id]);
        
        //删除pms
        Pms::deleteAll(['type' => 4,'receive_id' => $id]);

        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
    }

    //获取未读消息数量
    public function actionNoticeNum()
    {
        $num = UserFeedback::find()->where(["status" => "0"])->count();
        echo $num;
    }


    /**
     * Finds the UserFeedback model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return UserFeedback the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserFeedback::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
