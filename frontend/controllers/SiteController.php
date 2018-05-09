<?php

namespace frontend\controllers;

use Yii;
use frontend\controllers\BaseController;
use frontend\models\WebPage;
use common\functions\Tools;
use common\components\Wechat;
use common\models\User;
use common\models\ThirdLogin;
use common\models\Comment;
use common\models\LoginForm;
use common\models\ProductDetails;
use common\functions\Functions;
use common\models\Attachment;
use common\components\Youtube;

class SiteController extends BaseController
{
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex($cateId='')
    {
        $webPage   = new WebPage();

        //热搜列表
        $searchList = $webPage->getHotKeyword(10);
        
        //产品分类列表
        $productCate = $webPage->getProductCateList();
        //推荐产品列表
        $params = [
            'recommend' => 1,
            'pageSize'  => 10
        ];
        $productList = $webPage->newProductList($params);

        //品牌分类列表
        $brandCate = $webPage->getBrandCateList();
        //推荐品牌列表
        $brandList = $webPage->getBrandList($page = '1',$pageSize = '12',$cateId = '',$recommend = '1',$orderBy = 'hot desc');

        //文章分类列表
        $articleCate = $webPage->getArticleCateList(1);
        //推荐文章列表
        $articleList = $webPage->getArticleList($page = '1',$pageSize = '10',$cateId = '',$keyword = '',$hotId = '',$recommend = '',$orderBy = 'created_at desc');

        //广告列表
        $advertisementList = [
            "main1" => $webPage->getAdList($type = "site/index",$position = "main",$sort = "1"),
            "main2" => $webPage->getAdList($type = "site/index",$position = "main",$sort = "2"),
            "main3" => $webPage->getAdList($type = "site/index",$position = "main",$sort = "3"),
        ];

        //标题修改
        $this->GLOBALS['title'] = '颜究院——专业的护肤品成分查询、护肤品推荐网站';
        $this->GLOBALS['description'] = '颜究院，一个专业的护肤品成分查询、护肤品成分分析平台，根据不同的成分、功效、安全风险提供个性化的护肤品品牌推荐，以及不同肤质的护肤心得，一起科学护肤，健康护肤。';
        $this->GLOBALS['keywords'] = '护肤品成分，护肤品推荐，护肤品成分查询，颜究院';

        return $this->renderPartial('index.htm', [
            'searchList' => $searchList,
            'productCate' => $productCate,
            'productList' => $productList,
            'brandCate' => $brandCate,
            'brandList' => $brandList,
            'articleCate' => $articleCate,
            'articleList' => $articleList,
            'advertisementList' => $advertisementList,
            'GLOBALS' => $this->GLOBALS,
            'Tools' => new Tools,
        ]);
    }

    //ajax返回数据
    public function actionAjaxIndexData($type,$cateId)
    {
        $webPage   = new WebPage();
        $list = array();

        if($type == 'product'){
            $params = [
                'recommend' => 1,
                'pageSize'  => 10,
                'cateId'    => $cateId
            ];
            $list = $webPage->newProductList($params);
        }else if($type == 'brand'){
            $list = $webPage->getBrandList($page = '1',$pageSize = '12',$cateId ,$recommend = '1',$orderBy = 'hot desc');
        }else if($type == 'article'){
            $list = $webPage->getArticleList($page = '1',$pageSize = '10',$cateId ,$keyword = '',$hotId = '',$recommend = '',$orderBy = 'created_at desc');
        }

        $result = $this->renderPartial('data.htm', [
            'type' => $type,
            'list' => $list,
            'GLOBALS' => $this->GLOBALS,
            'Tools' => new Tools,
        ]);

        echo json_encode($result);
    }

    public function actionLogin()
    {
        //通过授权跳转网页进入
        if(isset($_GET['code'])) {

            if(Yii::$app->params['environment'] == "Development"){
                die("请用正式环境操作");
            }

            $appId          = Yii::$app->params['weixinLogin']['APPID'];
            $appSecret      = Yii::$app->params['weixinLogin']['APPKEY'];
            $option         = ['appid' => $appId, 'appsecret' => $appSecret];
            $wexin          = new Wechat($option);
            $access_token = $wexin->getOauthAccessToken();

            //检验授权凭证（access_token）是否有效
            $data = $wexin->checkAvail($access_token['access_token'],$access_token['openid']);

            if($data['errcode'] != '0' || $data['errmsg'] != 'ok'){
                //刷新access_token
                $access_token = $wexin->getOauthRefreshToken($access_token['refresh_token']);
            }

            //得到拉取用户信息
            $userData = $wexin->getOauthUserinfo($access_token['access_token'],$access_token['openid']);
            $user_id = self::thirdLogin($userData,$access_token);

            //处理评论
            $session = \Yii::$app->session;
            $comment_list = $session->get('comment_list');
            if($comment_list['comment']){
                self::addComment($user_id,$comment_list['post_id'],$comment_list['comment'],$comment_list['image']);
            }

            if($comment_list['post_id']){
                //跳转到评论页
                return $this->redirect(["product/details", 'id' => $comment_list['post_id'] ,'#'=>'comment']);
            }else{
                return $this->redirect(["site/index"]);
            }

        }else{
            return $this->renderPartial('login.htm', [
                'GLOBALS' => $this->GLOBALS,
                'Tools' => new Tools,
            ]);
        }
    }

    public  function actionDownload(){

        $sql = "SELECT downloadUrl FROM {{%app_version}} WHERE status =1 and type=1 ORDER BY  id DESC limit 1";
        $android_url    = Yii::$app->db->createCommand($sql)->queryScalar();

        $sql = "SELECT downloadUrl FROM {{%app_version}} WHERE status =1 and type=2 ORDER BY  id DESC limit 1";
        $ios_url    = Yii::$app->db->createCommand($sql)->queryScalar();

        return $this->renderPartial('download.htm', [
            'android_url' => $android_url,
            'ios_url' => $ios_url,
            'GLOBALS' => $this->GLOBALS,
        ]);
    }


    //添加评论
    static function addComment($user_id,$post_id,$comment,$image){

        $model = new Comment();
        $model->user_id = $user_id;
        $model->post_id = $post_id;
        $model->type = "1";
        $model->comment = $comment;
        $model->user_type = '0';
        $model->referer = 'h5';
        if($model->save(false)){
            //用户名同步
            $model = Comment::findOne($model->id);
            $userInfo = User::findOne($model->user_id);
            $model->author = $userInfo->username;
            $model->save(false);

            //图片附件
            if($image){
                $attach = new Attachment();
                $attach->cid = $model->id;
                $attach->uid = $user_id;
                $attach->attachment = $image;
                $attach->dateline = time();
                $attach->save(false);
            }

            //更新评论数
            $updateSql  = " UPDATE {{%product_details}} P SET P.comment_num = (SELECT COUNT(id) FROM {{%comment}}  WHERE type = '1' AND post_id = '$post_id' AND status = 1)  WHERE P.id = '$post_id'";
            $return = Yii::$app->db->createCommand($updateSql)->execute();
        }
    }

    //微信用户信息处理
    static function thirdLogin($userData,$access_token){

        $thirdInfo      = [];
        $thirdLogin     = ThirdLogin::find()->where("type = 'weixin' AND openid = :openid", [':openid' => $access_token['openid']])->asArray()->orderBy('id DESC')->one();

        if (empty($thirdLogin)){
            $thirdObj           = new ThirdLogin();
            $thirdObj->type     = 'weixin';
            $thirdObj->openid   = $access_token['openid'];
            $thirdObj->unionid  = $userData['unionid'];
            $thirdObj->save();

            $userObj            = new User();
            $userObj->username  = Tools::userTextEncode($userData['nickname']);
            $userObj->img      = Functions::uploadUrlimg($userData['headimgurl']);
            $userObj->mobile = 'weixin';
            $userObj->status = '1';
            $userObj->referer = 'H5';
            //普通用户性别，1为男性，2为女性
            $userObj->sex = $userData['sex'] == 2 ? 0 : $userData['sex'];
            $userObj->city = $userData['city'];
            $userObj->province = $userData['province'];
            $userObj->setPassword($access_token['openid']);
            $userObj->generateAuthKey();
            $userObj->save();

            $userObj->mobile = 'weixin' . $userObj->id;
            $userObj->save();

            $thirdObj->user_id = $userObj->id;
            $thirdObj->save();
            $uid        = $userObj->id;
            $username   = $userObj->username;
        }else{
            $userArr = User::find()->where("id = :userId", [':userId' => $thirdLogin['user_id']])->one();
            $uid        = $userArr['id'];
            $username   = $userArr['username'];
        }

        $model = new LoginForm();
        $login = [];
        $login['LoginForm']['mobile']   = 'weixin' . $uid;
        $login['LoginForm']['password'] = $access_token['openid'];

        if ($model->load($login) && $model->login()) {
            return Yii::$app->user->identity->getId();
        }

    }

    //模拟登陆
    public function actionTest(){

        $url       = 'https://www.bilibili.com/video/av15360947/';    
        $youtube   = new Youtube();
        $return    = $youtube->uploadFile($url);
        
        var_dump($return);die;
    }


    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
