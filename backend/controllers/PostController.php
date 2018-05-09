<?php

namespace backend\controllers;

use Yii;
use common\models\Post;
use common\models\PostSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\PostLike;
use backend\models\CommonFun;
use common\models\User;
use common\functions\ReplyFunctions;
use common\models\Attachment;
use common\models\Comment;

/**
 * PostController implements the CRUD actions for Post model.
 */
class PostController extends Controller
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
     * Lists all Post models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PostSearch();
        $search = Yii::$app->request->queryParams;
        
        if (empty($search) || (empty($search['PostSearch']) && (isset($search['page']) || isset($search['sort'])))) {
            $search['PostSearch']['user_type'] = '1';
        }
        $dataProvider = $searchModel->search($search);
        
        //更新状态
        Yii::$app->db->createCommand()->update('{{%post}}', ['is_read' => 1])->execute();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Post model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        //图片列表
        $imgArr = Attachment::find()->select("attachment")->where("cid = $id AND type = 2")->asArray()->column();
        $img = '';
        foreach ($imgArr as $key=>$val) {
            $img .= '<img src="'.Yii::$app->params['uploadsUrl'].$val.'" style="width:150px;height:150px">&emsp;';
        }
        return $this->render('view', [
            'model' => $this->findModel($id),
            'img' => $img
        ]);
    }

    /**
     * Creates a new Post model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Post();
        $post = Yii::$app->request->post();
        
        if ($model->load($post) && $model->save()) {
            //保存多张图片附件
            if (!empty($post['img']['pic_list'])) {
                foreach ($post['img']['pic_list'] as $key=>$val) {
                    $imagesize = getimagesize(Yii::$app->params['uploadsUrl'].$val);
                    
                    if ($key == '0') {
                        $model->picture = $val;
                        $model->ratio = $imagesize['0']/$imagesize['1'];
                        $model->save();
                    }
                    $img = new Attachment();
                    $img->cid = $model->id;
                    $img->uid = $model->user_id;
                    $img->attachment = $val;
                    $img->type = '2';
                    $img->dateline = time();
                    $img->ratio = $imagesize['0']/$imagesize['1'];
                    $img->save();
                }
            }           
            //更新帖子数
            $TopicUpdateSql  = "UPDATE {{%topic}} t SET t.post_num = (SELECT COUNT(*) FROM {{%post}}  WHERE topic_id = '$model->topic_id'  AND status = '1')  WHERE t.id = '$model->topic_id'";
            Yii::$app->db->createCommand($TopicUpdateSql)->execute();
            
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Post model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = Yii::$app->request->post();

        if ($post) {
            //处理被编辑过的图片
            $oldImgArr = Attachment::find()->select("attachment")->where("cid = $id AND type = 2")->orderBy('aid')->asArray()->column();
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
                    $img->type = '2';
                    $img->dateline = time();
                    $img->save();
                }
            }
            
            //获取修改后图片比例
            $nowImgArr = Attachment::find()->select("aid,attachment")->where("cid = $id AND type = 2")->orderBy('aid DESC')->asArray()->all();
            if ($nowImgArr) {
                foreach ($nowImgArr as $key=>$val) {
                    $imagesize = getimagesize(Yii::$app->params['uploadsUrl'].$val['attachment']);
                    
                    if ($key == '0') {
                        $model->picture = $val['attachment'];
                        $model->ratio = $imagesize['0']/$imagesize['1'];
                        $model->save();
                    }
                    $img = Attachment::findOne($val['aid']);
                    $imagesize = getimagesize(Yii::$app->params['uploadsUrl'].$val['attachment']);
                    $img->ratio = $imagesize['0']/$imagesize['1'];
                    $img->save();
                }
            }
        }
        
        if ($model->load($post) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Post model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $topicId = $model->topic_id;
        $model->delete();
        
        //修改pms状态
        $pmsUpdateSql  = "UPDATE {{%pms}} SET is_delete = 1 WHERE type = 1 AND relation_id = $id";
        Yii::$app->db->createCommand($pmsUpdateSql)->execute();
        
        //更新帖子数
        $TopicUpdateSql  = "UPDATE {{%topic}} t SET t.post_num = (SELECT COUNT(*) FROM {{%post}}  WHERE topic_id = '$topicId'  AND status = '1')  WHERE t.id = '$model->topic_id'";
        Yii::$app->db->createCommand($TopicUpdateSql)->execute();
        
        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
    }

    /**
     * Finds the Post model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Post the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Post::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    //帖子点赞
    public function actionCommentLike()
    {
        $data = Yii::$app->request->post();
        $postId = $data['postId'];
        $topicId = $data['topicId'];
    
        $post = Post::findOne($postId);
    
        if (Yii::$app->request->isAjax && $post) {
    
            //已点赞马甲列表
            $postLike = PostLike::find()
            ->select('user_id')
            ->where("post_id = $postId")
            ->asArray()
            ->column();
    
            //马甲列表
            $shadowList = CommonFun::getShadowList();
            $shadowList = array_keys($shadowList);
    
            $ids = array_values(array_diff($shadowList, $postLike));
    
            if (empty($ids)) {
                return json_encode(['status' => 1, 'data' => '所有马甲都已点赞！']);
            }
    
            $index = rand(0,count($ids)-1);
            $user = User::findOne($ids[$index]);
            $model = new PostLike();
            $model->user_id = $user->id;
            $model->post_id = $postId;
            $model->topic_id = $topicId;
            $model->save(false);

            $this->actionUpdateData($postId);
    
            $data = ['status' => '0', 'message' => '点赞成功', 'username' => $user->username,'userId' => $user->id,'likeNum' => $post->like_num];
            //添加通知
            ReplyFunctions::reply($user->id,$postId,ReplyFunctions::$POST_LIKE,'',$post->user_id,$postId);
    
            return json_encode($data);
        }
    }
    
    //更新帖子点赞数
    public function actionUpdateData($postId)
    {
        $VideoLikeUpdateSql  = "UPDATE {{%post}} p SET p.like_num = (SELECT COUNT(*)  FROM {{%post_like}}  WHERE  post_id = '$postId') WHERE p.id = '$postId'";
        Yii::$app->db->createCommand($VideoLikeUpdateSql)->execute();
    }
    
    //多图上传
    public function actionUploadImg($imgFilename){
        $data['status'] = "0";
    
        if (CommonFun::uploadImg($imgFilename)) {
            $data = CommonFun::uploadImg($imgFilename);
        }
         
        echo json_encode($data);
    }
    
    //获取未读消息数量
    public function actionUnreadNum()
    {
        $num = Post::find()->joinWith('user u')->where(["is_read" => "0","u.admin_id" => "0"])->count();
        echo $num;
    }
}
