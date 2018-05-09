<?php

namespace backend\controllers;

use Yii;
use common\models\Ask;
use common\models\AskSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\User;
use common\models\AskReply;
use common\models\ProductDetails;
use common\models\Comment;
use common\functions\Functions;
use common\functions\ReplyFunctions;
use common\functions\Tools;
use backend\models\CommonFun;

/**
 * AskController implements the CRUD actions for Ask model.
 */
class AskController extends Controller
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
     * Lists all Ask models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AskSearch();
        $params = Yii::$app->request->queryParams;
        //默认筛选真实用户
        if (empty($params) || (empty($search['AskSearch']) && isset($params['page']))) {
            $params['AskSearch']['userType'] = '1';
        }
        $dataProvider = $searchModel->search($params);

        $count = $dataProvider->totalCount;
        $cache = Yii::$app->cache;
        $cache->set('reply_num',$count,43200*30);

        $replyModel = new AskReply();
        $askModel = new Ask();
        $shadowList = CommonFun::getShadowList();
        
        //更新状态
        Yii::$app->db->createCommand()->update('{{%ask}}', ['is_read' => 1])->execute();
        Yii::$app->db->createCommand()->update('{{%ask_reply}}', ['is_read' => 1])->execute();

//修改admin_id
//         $ask = Ask::find()->all();
//         foreach ($ask as $key=>$val) {
//             $user = User::findOne($val->user_id);
//             if (!empty($user)) {
//                 if ($user->admin_id != 0) {
//                     $ask2 = Ask::findOne($val->askid);
//                     $ask2->admin_id = $user->admin_id;
//                     $ask2->save(false);
//                 }
//             }
//         }
//         $reply = AskReply::find()->all();
//         foreach ($reply as $key=>$val) {
//             $user = User::findOne($val->user_id);
//             if (!empty($user)) {
//                 if ($user->admin_id != 0) {
//                     $reply2 = AskReply::findOne($val->replyid);
//                     $reply2->admin_id = $user->admin_id;
//                     $reply2->save(false);
//                 }
//             }
//         }
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'replyModel' => $replyModel,
            'askModel' => $askModel,
            'shadowList' => $shadowList,
        ]);
    }

    //获取问答数量
//     public static function actionGetReplyNum()
//     {
//         $count1 = ASK::find()->count();
//         $count2 = AskReply::find()->count();
//         $count = $count1 + $count2;

//         $cache = Yii::$app->cache;
//         $old_count = $cache->get('reply_num');

//         echo $count - $old_count;
//     }

    /**
     * Displays a single Ask model.
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
     * Creates a new Ask model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($url)
    {
        $model = new Ask();
        if ($model->load(Yii::$app->request->post()) && $model->save(false)) {
            //处理其他字段
            $userInfo = User::findOne($model->user_id);
            $models = $this->findModel($model->askid);
            $productInfo = ProductDetails::findOne($model->product_id);
            $models->username = $userInfo->username;
            $models->product_name = $productInfo->product_name;
            $models->subject = $models->content;
            $models->status = '1';
            $models->add_time = time();
            $models->admin_id = yii::$app->user->id;
            $models->save(false);
            
            //添加通知
            $comment_user = Comment::find()->select('user_id')->where("post_id = '$model->product_id' AND type = '1' AND parent_id = '0'")->asArray()->column();
           if (!empty($comment_user)) {
               $user_str = Functions::db_create_in($comment_user,'receive_id');
               $start_time     = strtotime(date('Y-m-d'));
               $end_time       = $start_time + 86400;
               $receive_sql    = "SELECT receive_id FROM {{%pms_log}}  WHERE $user_str AND created_at >= '$start_time' AND created_at < '$end_time'";
               $receive_ids    = Yii::$app->db->createCommand($receive_sql)->queryColumn();
               $receive_arr    = array_diff($comment_user,$receive_ids);
               if(!empty($receive_arr)){
                   foreach($receive_arr as $k => $v){
                       ReplyFunctions::reply($model->user_id,$model->askid,ReplyFunctions::$USER_ASK,$models->content,$v,$model->askid);
                   }
               }
           }
            
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
     * Updates an existing Ask model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->askid]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Ask model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id,$url)
    {
        $this->findModel($id)->delete();
        //修改pms状态
        $this->actionUpdatePms($id,1);
        //删除关联回答的
        $model = AskReply::find()->where("askid = $id")->all();
            foreach ($model as $key=>$val) {
                $reply = AskReply::findOne($val->replyid);
                $reply->delete();
                //修改pms状态
                $this->actionUpdatePms($val->replyid,2);
            }

        if (isset($url)) {
            return $this->redirect([$url,'#'=>'view-tab']);
        }
    }

    //批量删除
    public function actionDeleteAll(){
        if($_POST['list']){
            foreach($_POST['list'] as $val){
                if($val['type'] == 1){
                    $this->findModel($val['askid'])->delete();
                    //修改pms状态
                    $this->actionUpdatePms($val['askid'],1);
                    //删除关联回答的
                    $model = AskReply::find()->where("askid = {$val['askid']}")->all();
                    foreach ($model as $key=>$val) {
                        $reply = AskReply::findOne($val->replyid);
                        $reply->delete();
                        //修改pms状态
                        $this->actionUpdatePms($val->replyid,2);
                    }
                }else{
                    //回答扣颜值（回答别人的问题）
                    $reply = AskReply::findOne($val['replyid']);
                    $userId = $reply->user_id;
                    $askUserId = $reply->ask->user_id;

                    if (User::findOne($userId) && $userId != $askUserId) {
                        Functions::updateMoney($userId,-10,'回答删除',2);
                    }
                    //删除回答的
                    $model = AskReply::findOne($val['replyid']);
                    $model->delete();
                    //修改pms状态
                    $this->actionUpdatePms($val['replyid'],2);
                }
            }
            echo 1;
        }
    }

    /**
     * Finds the Ask model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Ask the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ask::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    //获取未读消息数量
    public function actionUnreadNum()
    {
        $num = Ask::find()->where(["is_read" => "0","admin_id" => "0"])->count() + AskReply::find()->where(["is_read" => "0","admin_id" => "0"])->count();
        echo $num;
    }
    
    //评论点赞
    public function actionCommentLike()
    {
        $post = Yii::$app->request->post();
        $askId = $post['askId'];
        $replyId = $post['replyId'];
    
        $reply = AskReply::findOne($replyId);

        if (Yii::$app->request->isAjax && $reply) {
    
            //已点赞马甲列表
            $askLike = (new \yii\db\Query())
            ->select('user_id')
            ->from('{{%ask_like}}')
            ->where('reply_id = :reply_id', [':reply_id' => $replyId])
            ->column();

            //马甲列表
            $shadowList = CommonFun::getShadowList();
            $shadowList = array_keys($shadowList);
    
            $ids = array_values(array_diff($shadowList, $askLike));
    
            if (empty($ids)) {
                return json_encode(['status' => 1, 'data' => '所有马甲都已点赞！']);
            }
    
            $index = rand(0,count($ids)-1);
            $user = User::findOne($ids[$index]);
            $time = time();
            
            $insert = "INSERT INTO {{%ask_like}} (user_id,ask_id,reply_id,referer,created_at,updated_at) VALUES ($user->id,$askId,$replyId,'',$time,$time)";
            Yii::$app->db->createCommand($insert)->execute();
            
            //点赞数据
            $numSql   = "SELECT COUNT(*)  FROM {{%ask_like}}  WHERE  reply_id = '$replyId'";
            $num     = Yii::$app->db->createCommand($numSql)->queryScalar();
            if($num == 11){
                Functions::updateMoney($reply->user_id,20,'答案点赞',2);
            }
            $updateSql  = "UPDATE {{%ask_reply}} SET like_num = '$num' WHERE replyid = '$replyId'";
            Yii::$app->db->createCommand($updateSql)->execute();

            //点赞消息
            ReplyFunctions::reply($user->id,$reply->askid,ReplyFunctions::$USER_ASK_REPLY_LIKE,'',$reply->user_id,$reply->replyid);
    
            $data = ['status' => '0', 'message' => '点赞成功', 'username' => $user->username,'userId' => $user->id];
    
            return json_encode($data);
        }
    }
    
    //更新pms状态
    public function actionUpdatePms($id,$type)
    {
        if ($type == '1') {
            //删问题
            $pmsUpdateSql  = "UPDATE {{%pms}} SET is_delete = 1 WHERE type = 5 AND log_id = $id";
        } else {
            //删回答
            $pmsUpdateSql  = "UPDATE {{%pms}} SET is_delete = 1 WHERE (type = 6 AND log_id = $id) OR (type = 9 AND log_id = $id)";
        }
        
        Yii::$app->db->createCommand($pmsUpdateSql)->execute();
    }
}
