<?php
namespace frontend\controllers;

use yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\base\Object;
use yii\db\Command;
use common\components\Wechat;
use frontend\models\WebPage;
use common\functions\SearchProduct;

/**
 * 公共加载的控制器
 */
class BaseController extends Controller
{
    public $GLOBALS;
    public $weixinObj;
    //初使化加载
    function  init(){
        $this->GLOBALS = Yii::$app->params;
        $this->GLOBALS['equipment']        = self::getEquipment();

        //手机打开跳转H5
        if($this->GLOBALS['equipment'] == 'IOS' || $this->GLOBALS['equipment'] == 'Android'){

            $url = $this->GLOBALS['mfrontendUrl'];
            if($this->id == 'product'){
                if(isset($_GET['id'])){
                    $url = $this->GLOBALS['mfrontendUrl'] . "product/" . $_GET['id'] . ".html";
                }else{
                    $cateId = isset($_GET['cateId']) ? $_GET['cateId'] : '';
                    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
                    $url = $this->GLOBALS['mfrontendUrl'] . "product/search?cateId=" . $cateId . "&keyword=" . $keyword;
                }
            }else if($this->id == 'article' && isset($_GET['id'])){
                $url = $this->GLOBALS['mfrontendUrl'] . "article/" . $_GET['id'] . ".html";
            }else if(isset($this->module->requestedRoute) && $this->module->requestedRoute == 'site/download'){
                $url = $this->GLOBALS['mfrontendUrl'] . "site/download-guide";
            }

            header('Location:'.$url);
            exit;
        }

        $this->GLOBALS['title'] = '颜究院';
        $this->GLOBALS['description'] = '';
        $this->GLOBALS['keywords'] = '';
        $this->GLOBALS['controller']   = $this->id;

        $this->GLOBALS['userInfo']         = [];
        if (!Yii::$app->user->isGuest) {
            $this->GLOBALS['userInfo'] = ['uid' => Yii::$app->user->identity->id,'username' =>  Yii::$app->user->identity->username];
        }
        
        $this->GLOBALS['csrfToken']    = Yii::$app->request->csrfToken;
        //微信相关
        $appId      = Yii::$app->params['OfficialAccounts']['APPID'];
        $appSecret  = Yii::$app->params['OfficialAccounts']['APPKEY'];
        $option     = ['appid' => $appId, 'appsecret' => $appSecret];
        $this->weixinObj  = new Wechat($option);
        $this->GLOBALS['jsApi']            = [];


        $webPage = new WebPage();
        //产品搜索词列表
        $this->GLOBALS['productWord'] = $webPage->getHotKeyword(5);
        //文章热词列表
        $this->GLOBALS['articleWord'] = $webPage->getHotwordList(5);

    }
    /*
        获取当前设备
     */
    static public function getEquipment(){
        $equipment = 'PC';
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
            $equipment = 'IOS';
        }elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
            $equipment = 'Android';
        }
        return $equipment;
    }
    /*
        微信公共登录方法 
     */
    public function wxLogin(){
        //获取微信分享接口参数
        $url        = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $this->weixinShare();
        //判断是否登陆
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') && Yii::$app->user->isGuest) {
            // 此处为自动登录
            $data   =  $this->weixinObj->getOauthAccessToken();
            if (empty($data['unionid'])) {
                return $this->weixin($url);
            }
            $this->GLOBALS['userInfo']   =    Yii::$app->runAction('/weixin/wxlogin',['data'=> json_encode($data)]);
        }
       
    }
    //微信授权 
    public function weixin($back_url = '')
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $appId = Yii::$app->params['OfficialAccounts']['APPID'];
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appId.'&redirect_uri='.urlencode($back_url).'&response_type=code&scope=snsapi_userinfo&state=getbaseinfo#wechat_redirect';
            $this->redirect($url);
        }
    }
    //微信分享-不登录下
    public function weixinShare()
    {
        //获取微信分享接口参数
        $url        = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $type       = 'share';
        $this->GLOBALS['jsApi']      =    $this->weixinObj->getToken($url,$type);
    }




    //ajax返回数据
    public function actionAjaxData($type,$page = "1")
    {
        $webPage   = new WebPage();

        if($type == 'product'){
            //推荐产品
            $recommendProduct = $webPage->getRecommendProduct($page,$pageSize = "5");

            $result = $this->renderPartial('/common/recommenb_product.htm', [
                'recommendProduct' => $recommendProduct,
                'type' => "ajax",
                'GLOBALS' => $this->GLOBALS,
            ]);

        }else if($type == 'article'){
            //推荐文章
            $hotArticle = $webPage->getHotArticle($page,$pageSize = "5",$_GET['id']);

            $result = $this->renderPartial('/common/recommenb_article.htm', [
                'hotArticle' => $hotArticle,
                'type' => "ajax",
                'GLOBALS' => $this->GLOBALS,
            ]);
        }else if($type == 'product-search'){
            $result = SearchProduct::associative($_GET['title'],5);
        }else if($type == 'article-search'){
            //文章联想词列表
            $result = $webPage->getArticleWord($_GET['title']);

        }

        echo json_encode($result);
    }

}