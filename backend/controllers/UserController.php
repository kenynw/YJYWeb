<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\UserSearch;
use common\models\Comment;
use common\models\Admin;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\functions\NoticeFunctions;
use common\functions\Tools;
use common\models\Skin;
use common\functions\Skin as Skins;
use common\models\UserBan;
use common\models\Ask;
use common\models\AskReply;
use common\functions\ReplyFunctions;
use common\models\Pms;
use common\models\UserSkin;
use backend\components\AdminLog;
use yii\base\Object;

class UserController extends Controller
{
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
                    //'imagePathFormat' => "../../frontend/web/uploads/photo/{yyyy}{mm}{dd}/{time}{rand:6}",//上传图片的路径
                    'imagePathFormat' => $path."photo/{yyyy}{mm}{dd}/{time}{rand:6}",
                    // 'imagePathFormat' => "/uploads/banner/{time}{rand:6}",
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        //查询是否存在
        $time = time();
        $cond = ['and',['!=', 'expiration_time', 0],['<', 'expiration_time', $time]];
        $list = UserBan::find()->select("user_id")->where($cond)->asArray()->column();

        //更新状态
        if($list){
            UserBan::deleteAll($cond);
            User::updateAll(['status' => 1], ['in','id',$list]);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    //用户，马甲详情页
    public function actionView($id)
    {
        $model = $this->findModel($id);

        //所属账号
        $account = Admin::find()->select("username")->where(['id'=>$model->admin_id])->asArray()->scalar();

        //产品点评数
//         $product_comment_num1 = Comment::find()->where(['user_id'=>$model->id,'type'=>1,'status'=>1])->andWhere('parent_id = 0')->count(); 
//         $product_comment_num2 = Comment::find()->where(['user_id'=>$model->id,'type'=>1,'status'=>1])->andWhere('parent_id != 0')->groupBy('first_id')->count();
//         $product_comment_num = $product_comment_num1 + $product_comment_num2;
        $product_comment_num = Comment::find()->where(['user_id'=>$model->id,'type'=>1,'status'=>1,'parent_id'=>0])->count();

        //文章点评数
//         $article_comment_num1 = Comment::find()->where(['user_id'=>$model->id,'type'=>2,'status'=>1])->andWhere('parent_id = 0')->count();
//         $article_comment_num2 = Comment::find()->where(['user_id'=>$model->id,'type'=>2,'status'=>1])->andWhere('parent_id != 0')->groupBy('first_id')->count();
//         $article_comment_num = $article_comment_num1 + $article_comment_num2;
        $article_comment_num = Comment::find()->where(['user_id'=>$model->id,'type'=>2,'status'=>1,'parent_id'=>0])->count();
        
        //问答数
        $ask_num = Ask::find()->where("user_id = $id")->count();
        $reply_num = AskReply::find()->where("user_id = $id")->count();

        //用户所属肤质
        $sql = "SELECT s.id,s.skin,s.explain FROM {{%user_skin}} us LEFT JOIN {{%skin}} s on us.skin_name=s.skin  WHERE uid = '$id'";
        $skin_info  = Yii::$app->db->createCommand($sql)->queryOne();

        $userinfo = array(
            'account' => $account,
            'product_comment_num' => $product_comment_num,
            'article_comment_num' => $article_comment_num,
            'ask_reply_num' => $ask_num + $reply_num,
            'skin_id' => $skin_info['skin'],
            'skin_name' => $skin_info['skin'] ? $skin_info['skin'].'('.$skin_info['explain'].')' : "",
            'feedback_num' => User::getFeedbackNum($id)
        );

        //肤质列表
        $skinArr = Skin::find()->asArray()->all();
        $skinList = [];
        foreach ($skinArr as $key=>$val){
            $skinList[$val['skin']] = $val['skin'].'('.$val['explain'].')';
        }
        
        //用户沟通记录
        $communicate = Pms::find()
                       ->joinWith('admin')
                       ->where("type = 8 AND receive_id = $id")
                       ->all();

        return $this->render('view', [
            'model' => $model,
            'userinfo' => $userinfo,
            'skinList' => $skinList,
            'communicate' => $communicate
        ]);
    }
    
    //创建马甲
    public function actionCreate()
    {
        $model = new User();
        if($data = Yii::$app->request->post('User')){

            //处理生日
            if($data['birth_date']){
                $model->birth_year = date('Y') - $data['birth_date'];
                $model->birth_month = rand(1, 12);
                $model->birth_day = rand(1, 28);
//                 $temp = explode("-",$data['birth_date']);
//                 $model->birth_year = $temp[0];
//                 $model->birth_month = $temp[1];
//                 $model->birth_day = $temp[2];
            }

            $model->username = Tools::userTextEncode($data['username']);
            $model->admin_id = Yii::$app->user->identity->id;
            $model->mobile = 'shadow';
            $model->status = '1';
            $model->referer = '';
            $model->img = $data['img'] ? $data['img'] : 'photo/member.png';
            $model->setPassword('shadow');
            $model->generateAuthKey();
            $model->mobile = 'shadow' . $model->admin_id;

            if($model->save()){
                //保存肤质
                if($data['skin_id']){
                    $this::updateSkin($data['skin_id'],$model->id);
                }

                return $this->redirect(['index', 'UserSearch[admin_id]' => $model->admin_id]);
            }
        }else{

            //肤质列表
            $skinArr = Skin::find()->asArray()->all();
            $skinList = [];
            foreach ($skinArr as $key=>$val){
                $skinList[$val['skin']] = $val['skin'].'('.$val['explain'].')';
            }

            return $this->renderAjax('create', [
                'model' => $model,
                'skinList' => $skinList,
            ]);
        }

    }

    //更新头像、头像状态、备注、状态、生日
    public function actionUpdate($id,$type)
    {
        $model = $this->findModel($id);

        $data = Yii::$app->request->post();
        if($type == "image"){
            $model->img = $data['User']['img'];
        }else if($type == "imgState"){
            $model->img = 'photo/member.png';
            $model->img_state = 1;
            //添加通知
            NoticeFunctions::notice($id,NoticeFunctions::$PUNISH_HEAD);
            //NoticeFunctions::JPushOne(['Alias' => $id, 'id' => $id, 'type' => '4', 'option' => 'imgDisable']);
        }else if($type == "remark"){
            $model->remark = Yii::$app->request->get('remark');
        }else if($type == "username"){

            //判断马甲名是否已存在
            $cond = ['and' ,['username' => Yii::$app->request->get('username')] ,  ['!=', 'id', $id] ];
            $check = User::find()->where($cond)->one();
            if($check){
                Yii::$app->getSession()->setFlash('error', '马甲名已存在');
                return $this->redirect(['view', 'id' => $model->id]);
            }

            $model->username = Yii::$app->request->get('username');
        }else if($type == "status"){
            $this::banned($id,Yii::$app->request->get('status'));

            $model->status = Yii::$app->request->get('status');
        }else if($type == "birth"){
            if(Yii::$app->request->get('birth')){
                $model->birth_year = date('Y') - Yii::$app->request->get('birth');
                $model->birth_month = rand(1, 12);
                $model->birth_day = rand(1, 28);
//                 $temp = explode("-",Yii::$app->request->get('birth'));
//                 $model->birth_year = $temp[0];
//                 $model->birth_month = $temp[1];
//                 $model->birth_day = $temp[2];
            }
        }

        if ($model->save(false)) {

            //同步其他表的用户名
            if($type == "username"){
                User::updateUsername($model->id,$model->username);
            }

            Yii::$app->getSession()->setFlash('success', '修改成功');
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    //修改肤质
    public function actionUpdateSkin($id,$skin_name){
        $this::updateSkin($skin_name,$id);

        Yii::$app->getSession()->setFlash('success', '修改成功');
        return $this->redirect(['view', 'id' => $id]);
    }

    //用户禁言修改
    static function banned($userId,$status){
        $time = time();
        //查询是否存在
        $model =  UserBan::findOne(['user_id' => $userId]);

        if($status == 1 && $model){
            UserBan::deleteAll(['user_id' => $userId]);
            return true;
        } elseif ($status == 2) {
            $expiration_time = 0;
            //禁言7天
            if($status == 2){
                $expiration_time = strtotime(date("Y-m-d")." +7days" );
            }

            if($model){
                $return = UserBan::updateAll(['expiration_time' => $expiration_time], ['user_id'=>$userId]);
            }else{
                $return = Yii::$app->db->createCommand()->insert('yjy_user_ban', ['user_id' => $userId, 'expiration_time' => $expiration_time, 'add_time' => $time])->execute();
            }

            if($return){               
                //推送消息处理
                NoticeFunctions::notice($userId,NoticeFunctions::$SHUTUP_7_DAY);
            }
        } else {
            //封号推送消息处理
            NoticeFunctions::notice($userId,NoticeFunctions::$KICK);
            return true;
        }
    }

    //用户肤质修改
    static function updateSkin($skin_name,$uid){
        $skin_list = str_split($skin_name);
        //肤质列表
        $skin_lists = Skins::$skinList;

        //随机肤质值
        $skin_val = array();
        foreach($skin_list as $key=>$val){
            $skin_val[] = mt_rand($skin_lists[$val][0],$skin_lists[$val][1]);
        }

        $sql = "SELECT * FROM {{%user_skin}} WHERE uid='$uid'";
        $info = Yii::$app->db->createCommand($sql)->queryOne();

        if($info){  
            $userSkin = UserSkin::findOne($uid);
//             $userSkin->uid = $uid;
            $userSkin->skin_name = $skin_name;
            $userSkin->dry = $skin_val[0];
            $userSkin->tolerance = $skin_val[1];
            $userSkin->pigment = $skin_val[2];
            $userSkin->compact = $skin_val[3];
            $userSkin->save();
            
//             $captchaSql   = "UPDATE  {{%user_skin}} SET skin_name = '$skin_name',dry = '$skin_val[0]',tolerance = '$skin_val[1]',pigment = '$skin_val[2]',compact = '$skin_val[3]' WHERE uid ='$uid'";
//             $update = Yii::$app->db->createCommand($captchaSql)->execute();
        }else{
            $userSkin = new UserSkin();
            $userSkin->uid = $uid;
            $userSkin->skin_name = $skin_name;
            $userSkin->dry = $skin_val[0];
            $userSkin->tolerance = $skin_val[1];
            $userSkin->pigment = $skin_val[2];
            $userSkin->compact = $skin_val[3];
            $userSkin->save();
//             $captchaSql   = "INSERT INTO  {{%user_skin}} (uid,skin_name,dry,tolerance,pigment,compact) VALUES ('$uid','$skin_name','$skin_val[0]','$skin_val[1]','$skin_val[2]','$skin_val[3]')";
//             $update = Yii::$app->db->createCommand($captchaSql)->execute();
        }
    }

    public function actionCheckName($id,$name){

        if($id){
            $cond = [ 'and',['!=', 'id', $id], 'username = "$name"'];
        }else{
            $cond = [ 'username' => $name];
        }

        $model = User::find()->where($cond)->one();
        if($model){
            echo 1;
        }
    }
    
    //用户沟通入口
    public function actionCommunicate($id){    
        //相关参数
        $user = User::findOne($id);
        $from_id = yii::$app->user->id;
        $post = Yii::$app->request->post();
        $content = $post['User']['communicate'];

        if (!empty($user)) {
            //入库pms,推送
            ReplyFunctions::reply($from_id,'0',ReplyFunctions::$USER_COMMUNICATE,$content,$id);
            NoticeFunctions::JPushOne(['Alias' => $id,'option' => 'communicate','id'=>0,'relation'=>'','type'=>'6','replaceStr' => $content ]);
        } else {
            echo "用户不存在";
            die;
        }
        
        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


}