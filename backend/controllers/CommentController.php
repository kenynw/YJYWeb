<?php

namespace backend\controllers;

use Yii;
use common\models\Comment;
use common\models\CommentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\User;
use common\models\CommentLike;
use common\models\ProductDetails;
use common\models\Article;
use common\functions\NoticeFunctions;
use common\functions\Tools;
use common\functions\ReplyFunctions;
use yii\base\Object;
use common\models\Attachment;
use common\functions\Functions;
use backend\models\CommonFun;
use yii\db\ActiveRecord;

/**
 * CommentController implements the CRUD actions for Comment model.
 */
class CommentController extends Controller
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
                    'imagePathFormat' => $path."comment_img/{yyyy}{mm}{dd}/{time}{rand:6}",
                    // 'imagePathFormat' => "/uploads/banner/{time}{rand:6}",
                ]
            ]
        ];
    }

    /**
     * Lists all Comment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CommentSearch();
        $search = Yii::$app->request->queryParams;
        if (empty($search) || (empty($search['CommentSearch']) && isset($search['page']))) {
            $search['CommentSearch']['user_type'] = '0';
        }
        $dataProvider = $searchModel->search($search);
        
        //更新状态
        Yii::$app->db->createCommand()->update('{{%comment}}', ['is_read' => 1])->execute();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Comment model.
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
     * Creates a new Comment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Comment();
        $post = Yii::$app->request->post();

        if ($model->load($post) && $model->save(false)) {
            //其他字段处理
            $model = $this->findModel($model->id);
            $userInfo = User::findOne($model->user_id);
            $command = ActiveRecord::getDb()->createCommand();
            
            $author = $userInfo->username;
            $user_type = $userInfo->admin_id ? "1" : "0";
            $command->update('yjy_comment', ['author'=> $author,'user_type' => $user_type], "id = $model->id");
            $command->execute();

            //数据处理
            if ($model->type == '1') {
                //添加产品评论操作记录
                $model->parent_id == '0' ? CommonFun::addAdminLogView('添加产品评论',$model->post_id) : false;
            } elseif ($model->type == '2') {

            }
            $this->actionUpdateData($model->id, $model->type, $model->post_id);
            
            //保存单张图片附件
            if (!empty($post['Comment']['img'])) {
                $img = new Attachment();
                $img->cid = $model->id;
                $img->uid = $model->user_id;
                $img->attachment = $post['Comment']['img'];
                $img->dateline = time();
                $img->save();
            }
            
            //保存多张图片附件
            if (!empty($post['img']['pic_list'])) {
                foreach ($post['img']['pic_list'] as $key=>$val) {
                    $img = new Attachment();
                    $img->cid = $model->id;
                    $img->uid = $model->user_id;
                    $img->attachment = $val;
                    $img->dateline = time();
                    $img->save();
                }
            }
            
            if ($model->first_id != '0' && $model->parent_id != '0') {
                //二级评论添加通知
                ReplyFunctions::reply($model->user_id,$model->parent_id,ReplyFunctions::$REPLY_OTHER_COMMENT,$model->comment,'',$model->id);
            }
            
            //帖子一级评论通知
            if ($model->first_id == '0' && $model->parent_id == '0' && $model->type == '4') {
                ReplyFunctions::reply($model->user_id,$model->post_id,ReplyFunctions::$REPLY_POST,$model->comment,'',$model->post_id);
            }

            $url = Yii::$app->request->post('jump_url');
            return $this->redirect([$url,"#" => "comment"]);
        } else {
            $shadowList = CommonFun::getShadowList();
            return $this->renderAjax('create', [
                'model' => $model,
                'shadowList' => $shadowList,
            ]);
        }
    }

    /**
     * Updates an existing Comment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $post = Yii::$app->request->post();
        $img = Attachment::find()->where("cid = $id AND type = 1")->one();

        if ($model->load($post) && $model->save(false)) {
            //其他字段处理
            $model = $this->findModel($model->id);
            $userInfo = User::findOne($model->user_id);            
            $command = ActiveRecord::getDb()->createCommand();
            
            $author = $userInfo->username;
            $user_type = $userInfo->admin_id ? "1" : "0";
            $command->update('yjy_comment', ['author'=> $author,'user_type' => $user_type], "id = $model->id");
            $command->execute();

            //保存图片附件
            if (!empty($post['Comment']['img'])) {   
                //原来有并且发生变化
                if ($img && $img->attachment != $post['Comment']['img']) {
                    $img->uid = $model->user_id;
                    $img->attachment = $post['Comment']['img'];
                    $img->dateline = time();
                    $img->save();
                } elseif (!$img) {
                    //原来没有
                    $img = new Attachment();
                    $img->cid = $model->id;
                    $img->uid = $model->user_id;
                    $img->attachment = $post['Comment']['img'];
                    $img->dateline = time();
                    $img->save();
                }
            }

            //处理被编辑过的图片
            $oldImgArr = Attachment::find()->select("attachment")->where("cid = $id AND type = 1")->orderBy('aid')->asArray()->column();
            $newImgArr = isset($post['img']) ? $post['img']['pic_list'] : [];
            
            $sameArr = array_intersect($oldImgArr,$newImgArr);
            
            $oldImgArr = array_diff($oldImgArr, $sameArr);
            $newImgArr = array_diff($newImgArr, $sameArr);

            if (!empty($oldImgArr)) {
                //被删
                foreach ($oldImgArr as $key=>$val) {
                    Attachment::deleteAll("cid = $id AND attachment = '$val'");
                }
            }
            if (!empty($newImgArr)) {
                //新增
                foreach ($newImgArr as $key=>$val) {
                    $img = new Attachment();
                    $img->cid = $model->id;
                    $img->uid = $model->user_id;
                    $img->attachment = $val;
                    $img->dateline = time();
                    $img->save();
                }
            }
            
            //添加产品评论操作记录
            CommonFun::addAdminLogView('修改产品评论',$model->post_id);

            $url = Yii::$app->request->post('jump_url');
            return $this->redirect([$url,"#" => "comment"]);
        } else {//var_dump($img);die;
            //马甲列表
            $shadowList = CommonFun::getShadowList();
            //图片
            if ($img) {
                $model->img = $img->attachment;
            }
            //多张图
            $imgs = Attachment::find()->select("attachment")->where("cid = $id AND type = 1")->orderBy('aid')->asArray()->column();
            
            return $this->renderAjax('update', [
                'model' => $model,
                'shadowList' => $shadowList,
                'imgs' => $imgs
            ]);
        }
    }

    //评论点赞
    public function actionCommentLike()
    {
        $data = Yii::$app->request->post();
        $postId = $data['postId'];
        $commentId = $data['commentId'];
        $type = $data['type'];

        $comment = Comment::findOne($commentId);

        if (Yii::$app->request->isAjax && $comment) {

            //已点赞马甲列表
            $CommentLike = CommentLike::find()
                ->select('user_id')
                ->where('comment_id = :comment_id and type = :type', [':comment_id' => $commentId,':type' => $type])
                ->asArray()
                ->column();

            //马甲列表
            $shadowList = CommonFun::getShadowList();
            $shadowList = array_keys($shadowList);

            $ids = array_values(array_diff($shadowList, $CommentLike));

            if (empty($ids)) {
                return json_encode(['status' => 1, 'data' => '所有马甲都已点赞！']);
            }

            $index = rand(0,count($ids)-1);
            $user = User::findOne($ids[$index]);
            $model = new CommentLike();
            $model->user_id = $user->id;
            $model->post_id = $postId;
            $model->comment_id = $commentId;
            $model->type = $type;
            $model->save(false);

            $comment->like_num = $comment->like_num + 1;
            $comment->save(false);
            $this->actionUpdateData($commentId, $type, $postId);
            
            $data = ['status' => '0', 'message' => '点赞成功', 'username' => $user->username,'userId' => $user->id,'likeNum' => $comment->like_num];
            //添加通知
            ReplyFunctions::reply($user->id,$commentId,ReplyFunctions::$REPLY_LIKE_COMMENT);

            return json_encode($data);
        }
    }
    
    //单项删除评论
    /**
     * Deletes an existing Comment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->status = Yii::$app->request->get('status');
    
        //相关参数
        $postId = $model->post_id;
        $userId = $model->user_id;
        
        if($model->save(false)){            
            //相关处理
            if ($model->status == '0') {
                if ($model->type == 1) {
                    //添加通知
                    NoticeFunctions::notice($model->user_id,NoticeFunctions::$PRODUCT_COMMENT_DELETED,$id,$id);
                    //产品扣颜值
                    Functions::updateMoney($userId,-10,'产品评论删除',2);                    
                } elseif ($model->type == 2) {
                    //添加通知
                    NoticeFunctions::notice($model->user_id,NoticeFunctions::$ARTICLE_COMMENT_DELETED,$id,$id);
                } elseif ($model->type == 3) {
                    //添加通知
                    NoticeFunctions::notice($model->user_id,NoticeFunctions::$VIDEO_COMMENT_DELETED,$id,$id);
                }

            }
            
            //删除二级评论
            $this->actionDeleteSecond($id,$model->type, $model->status);
            //更新评论数据
            $this->actionUpdateData($id, $model->type, $postId);
            //修改pms状态
            $this->actionUpdatePms($id);
    
            $url = Yii::$app->request->referrer.'#comment';
            return $this->redirect($url);
        } 
    }

    //批量删除评论
    public function actionDeleteAll()
    {
        if (Yii::$app->request->isAjax && Yii::$app->request->post()) {
            // 开启事务
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                $post = Yii::$app->request->post('id');
                foreach ($post as $key => $val) {
                    $model = $this->findModel($val);
                    $model->status = 0;
                    $model->save(false);
                    
                    //相关参数
                    $postId = $model->post_id;
                    $userId = $model->user_id;
                    
                    //相关处理
                    if ($model->type == 1) {
                        //添加通知
                        NoticeFunctions::notice($model->user_id,NoticeFunctions::$PRODUCT_COMMENT_DELETED,$val,$val);
                        //产品扣颜值
                        Functions::updateMoney($userId,-10,'产品评论删除',2);
                    } elseif ($model->type == 2) {
                        //添加通知
                        NoticeFunctions::notice($model->user_id,NoticeFunctions::$ARTICLE_COMMENT_DELETED,$val,$val);
                        //NoticeFunctions::JPushOne(['Alias' => $comment->user_id, 'id' => '', 'type' => '0', 'option' => 'comment_delete']);
                    } elseif ($model->type == 3) {
                        //添加通知
                        NoticeFunctions::notice($model->user_id,NoticeFunctions::$VIDEO_COMMENT_DELETED,$val,$val);
                    }
                    
                    //删除二级评论
                    $this->actionDeleteSecond($val,$model->type, $model->status);
                    //更新评论数据
                    $this->actionUpdateData($val, $model->type, $postId);
                    //修改pms状态
                    $this->actionUpdatePms($val);
                }
                
                $data['status'] = "1";
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollback();
                $data['status'] = "0";
            }
        }
        return json_encode($data);
    }
    
    //删除二级评论
    public function actionDeleteSecond($id,$type,$status)
    {
        $comment = Comment::find()->where("parent_id = $id AND type = $type")->all();
        if (!empty($comment)) {
            foreach ($comment as $key=>$val) {
                //删除或撤回
                $val->status = $status;
                $val->save();
                
                $this->actionDeleteSecond($val->id,$type,$status);
                //修改pms状态
                $this->actionUpdatePms($val->id);                            
            }
        }  
    }

    /**
     * Finds the Comment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Comment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Comment::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    //改变状态（产品页精华）
    public function actionChangeStatus()
    {
        $id = Yii::$app->request->post("id");
        $status = Yii::$app->request->post("status");
        $type = Yii::$app->request->post("type");        
    
        $data['status'] = "0";
    
        if($model = Comment::findOne($id)){
            if ($type == 'is_digest' && $status == '0') {
                //添加通知
                NoticeFunctions::notice($model->user_id,NoticeFunctions::$ADD_STAR,$id,$id);
                $product = ProductDetails::findOne($model->post_id);
                NoticeFunctions::JPushOne(['Alias' => $model->user_id, 'id' => $id, 'type' => '0', 'option' => 'digest','replaceStr' => $product->product_name]);
                //增加颜值分
                Functions::updateMoney($model->user_id,50,'精华点评',2);
            }
            
            $status = $status == 1 ? 0 : 1;
            $model->$type = $status;
            $model->save(false);
            $data['status'] = "1";
        }

        echo json_encode($data);
    }
    
    //更新评论点赞数，文章点赞数，文章评论数，产品评论数
    public function actionUpdateData($commentId,$type,$postId)
    {
        //更新评论点赞数
        $CommentUpdateSql  = "UPDATE {{%comment}} C SET C.like_num = (SELECT COUNT(*)  FROM {{%comment_like}}  WHERE  comment_id = '$commentId') WHERE C.id = '$commentId'";
        Yii::$app->db->createCommand($CommentUpdateSql)->execute();
        //更新文章、产品点赞数，评论数
        if ($type == '2') {
            $ArticleLikeUpdateSql  = " UPDATE {{%article}} P SET P.like_num = (SELECT SUM(like_num) FROM {{%comment}}  WHERE type = '2' AND post_id = '$postId'  AND status = '1')  WHERE P.id = '$postId'";
            Yii::$app->db->createCommand($ArticleLikeUpdateSql)->execute();
            $ArticleCommentUpdateSql  = " UPDATE {{%article}} P SET P.comment_num = (SELECT COUNT(*) FROM {{%comment}}  WHERE type = '2' AND post_id = '$postId'  AND status = '1' AND parent_id = '0')  WHERE P.id = '$postId'";
            Yii::$app->db->createCommand($ArticleCommentUpdateSql)->execute();
        } elseif ($type == '1') {
            $ProductCommentUpdateSql  = " UPDATE {{%product_details}} P SET P.comment_num = (SELECT COUNT(*) FROM {{%comment}}  WHERE type = '1' AND post_id = '$postId'  AND status = '1' AND parent_id = '0')  WHERE P.id = '$postId'";
            Yii::$app->db->createCommand($ProductCommentUpdateSql)->execute();
        } elseif ($type == '3') {
            $VideoCommentUpdateSql  = " UPDATE {{%video}} v SET v.comment_num = (SELECT COUNT(*) FROM {{%comment}}  WHERE type = '3' AND post_id = '$postId'  AND status = '1' AND parent_id = '0')  WHERE v.id = '$postId'";
            Yii::$app->db->createCommand($VideoCommentUpdateSql)->execute();
            $VideoLikeUpdateSql  = " UPDATE {{%video}} v SET v.like_num = (SELECT SUM(like_num) FROM {{%comment}}  WHERE type = '3' AND post_id = '$postId'  AND status = '1')  WHERE v.id = '$postId'";
            Yii::$app->db->createCommand($VideoLikeUpdateSql)->execute();
        } elseif ($type == '4') {
            $PostUpdateSql  = " UPDATE {{%post}} p SET p.comment_num = (SELECT COUNT(*) FROM {{%comment}}  WHERE type = '4' AND post_id = '$postId'  AND status = '1' AND parent_id = '0')  WHERE p.id = '$postId'";
            Yii::$app->db->createCommand($PostUpdateSql)->execute();
        }
    }
    
    //更新pms、notice_user状态
    public function actionUpdatePms($id)
    {
        //精华评论被删
        $pmsUpdateSql1  = "UPDATE {{%notice_user}} SET is_delete = 1 WHERE type = 3 AND relation_id = $id";
        Yii::$app->db->createCommand($pmsUpdateSql1)->execute();
        
        //一级被删
        $pmsUpdateSql2  = "UPDATE {{%pms}} SET is_delete = 1 WHERE (type = 7 AND relation_id = $id) OR (type = 2 AND relation_id = $id)";
        Yii::$app->db->createCommand($pmsUpdateSql2)->execute();
        
        //二级被删
        $pmsUpdateSql3  = "UPDATE {{%pms}} SET is_delete = 1 WHERE (type = 7 AND log_id = $id) OR (type = 2 AND log_id = $id)";
        Yii::$app->db->createCommand($pmsUpdateSql3)->execute();

    }
    
    //获取未读消息数量
    public function actionUnreadNum()
    {
        $num = Comment::find()->where(["is_read" => "0","user_type" => "0"])->count();
        echo $num;
    }
    
    //获取问题回复列表
    public function actionReplyList(){
        if($_POST['first_id']){
            $sql = "SELECT * FROM {{%comment}} WHERE first_id='{$_POST['first_id']}' AND status = 1 ORDER BY created_at";
            $comment  = Yii::$app->db->createCommand($sql)->queryAll();
            foreach ($comment as $key=>$val) {
                $comment[$key]['created_at'] = date('Y-m-d H:i:s',$val['created_at']);
                $comment[$key]['author'] = Tools::userTextDecode($comment[$key]['author']);
                $comment[$key]['comment'] = Tools::userTextDecode($comment[$key]['comment']);
                //父级信息
                $parentSql = "SELECT * FROM {{%comment}} WHERE id = {$val['parent_id']}";
                $parentComment  = Yii::$app->db->createCommand($parentSql)->queryOne();
                $comment[$key]['parent_user_id'] = $parentComment['user_id'];
                $comment[$key]['parent_admin_id'] = $parentComment['admin_id'];
                $comment[$key]['parent_username'] = Tools::userTextDecode($parentComment['author']);
                $comment[$key]['parent_status'] = $parentComment['status'];
            }

            echo json_encode($comment);
        }
    }
    
    //多图上传
    public function actionUploadImg($imgFilename){
        $data['status'] = "0";        

        if (CommonFun::uploadImg($imgFilename)) {
            $data = CommonFun::uploadImg($imgFilename);
        }        
         
        echo json_encode($data);
    }
}
