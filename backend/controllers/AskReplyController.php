<?php

namespace backend\controllers;

use Yii;
use common\models\AskReply;
use common\models\AskReplySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\User;
use common\models\Ask;
use common\functions\NoticeFunctions;
use common\functions\ReplyFunctions;
use common\functions\Functions;

/**
 * AskReplyController implements the CRUD actions for AskReply model.
 */
class AskReplyController extends Controller
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
                    'imagePathFormat' => $path."ask_reply_img/{yyyy}{mm}{dd}/{time}{rand:6}",
                ]
            ]
        ];
    }

    /**
     * Lists all AskReply models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AskReplySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AskReply model.
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
     * Creates a new AskReply model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AskReply();

        if ($model->load(Yii::$app->request->post()) && $model->save(false)) {

            //相关处理
            $userInfo = User::findOne($model->user_id);
            $models = $this->findModel($model->replyid);
            $models->username = $userInfo->username;
            $models->add_time = time();
            $models->admin_id = yii::$app->user->id;
            $models->save(false);
            
            //添加通知
            $ask = Ask::findOne($model->askid);
            if (!empty($ask)) {
                ReplyFunctions::reply($models->user_id,$model->askid,ReplyFunctions::$USER_ASK_REPLY,$models->reply,'',$model->replyid);
                NoticeFunctions::JPushOne(['Alias' => $ask->user_id,'option' => 'ask','id'=>$ask->askid,'relation'=>$ask->askid,'type'=>'5','replaceStr' => $ask->subject ]);
            }

            $url = Yii::$app->request->post()['AskReply']['url'];
            if (preg_match('{\D+\/view}', $url)) {
                return $this->redirect([$url,"#" => "view-tab"]);
            } else {
                return $this->redirect([$url]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing AskReply model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->replyid]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing AskReply model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        //回答扣颜值（回答别人的问题）
        $model = $this->findModel($id);
        $userId = $model->user_id;
        $askUserId = $model->ask->user_id;
        
        if (User::findOne($userId) && $userId != $askUserId) {
            Functions::updateMoney($userId,-10,'回答删除',2);
        }
        
        $this->findModel($id)->delete();
        
        //修改pms状态
        $pmsUpdateSql  = "UPDATE {{%pms}} SET is_delete = 1 WHERE (type = 6 AND log_id = $id) OR (type = 9 AND log_id = $id)";
        Yii::$app->db->createCommand($pmsUpdateSql)->execute();

        $url = Yii::$app->request->referrer.'#view-tab';
        return $this->redirect($url);
    }

    //获取问题回复列表
    public function actionReplyList(){

        if($_POST['askid']){
            $sql = "SELECT r.*,u.admin_id FROM {{%ask_reply}} r LEFT JOIN {{%user}} u ON r.user_id=u.id WHERE r.askid='{$_POST['askid']}' ORDER BY r.add_time desc";
            $reply_list  = Yii::$app->db->createCommand($sql)->queryAll();

            $result = $this->renderPartial('data.php', [
                'reply_list' => $reply_list,
            ]);
            echo json_encode($result);
        }
    }


    /**
     * Finds the AskReply model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AskReply the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AskReply::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
