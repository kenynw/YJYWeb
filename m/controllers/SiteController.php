<?php

namespace m\controllers;

use Yii;
use yii\web\Controller;
use m\models\WebPage;
use common\models\User;
use common\functions\Tools;
use common\functions\Skin;
use yii\web\NotFoundHttpException;
use common\functions\Functions;
use common\functions\Huodong;
use m\models\Mfunctions;
/**
 * Site controller
 */
class SiteController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        header("Content-type: text/html; charset=utf-8");
        $webPage   = new WebPage();
        $this->GLOBALS['TOP_SHOW'] = 'index';

        //热门搜索列表
        $searchList = $webPage->getHotKeyword(5);

        //分类列表
        $cateList = $webPage->getProductCateList();

        //推荐产品列表
        $productList = $webPage->getRecommendProduct(10);

        //推荐文章列表
        $articleList = Mfunctions::getArticleList('','','created_at DESC')['list'];
        
        //查询轮播
        $banner = Functions::getBannerList(3);

        //功效
         $effectList = Functions::effectList();

         $this->GLOBALS['OfficialAccounts']['share']['title'] = '专业的护肤品成分查询、护肤品推荐网站';
         $this->GLOBALS['OfficialAccounts']['share']['desc'] = '查成分、测肤质，颜究院根据肤质推荐安全有效的护肤品';

         //标题修改
        $this->GLOBALS['title'] = '颜究院——专业的护肤品成分查询、护肤品推荐网站';
        $this->GLOBALS['description'] = '颜究院，一个专业的护肤品成分查询、护肤品成分分析平台，根据不同的成分、功效、安全风险提供个性化的护肤品品牌推荐，以及不同肤质的护肤心得，一起科学护肤，健康护肤。';
        $this->GLOBALS['keywords'] = '护肤品成分，护肤品推荐，护肤品成分查询，颜究院';

        return $this->renderPartial('index103.htm', [
            'searchList'        => $searchList,
            'cateList'          => $cateList,
            'productList'       => $productList,
            'GLOBALS'           => $this->GLOBALS,
            'RecommendArticle'  => $articleList,
            'RecommendAsk'      => Mfunctions::getAskList('','','A.add_time desc','','5')['list'],
            'effectList'        => $effectList,
            'banner'            => $banner,
        ]);
    }
    /**
     * [actionSkinUnscramble 成份解读页面]
     * @return [type] [description]
     */
    public function actionSkinUnscramble()
    {
        return $this->renderPartial('skinunscramble.htm', [
            'GLOBALS' => $this->GLOBALS
        ]);
    }
    /**
     * [actionBatchNumber 批号说明]
     * @return [type] [description]
     */
    public function actionBatchNumber()
    {
        return $this->renderPartial('batchnumber.htm', [
            'GLOBALS' => $this->GLOBALS
        ]);
    }
    /**
     * [actionSkinTest 肤质测试]
     * @return [type] [description]
     */
    public function actionSkinTest(){
        //获取类型
        $type       = Yii::$app->request->get('type');
        $userId     = Yii::$app->request->get('user_id');
        $from       = Yii::$app->request->get('from');

        $type       = intval($type) ? intval($type) : 1;
        $userId     = intval($userId);
        $time       = time();
        
        $skinConfig = skin::$skinConfig;
        switch ($type) {
            case '2':
                $key     = 'tolerance';
                $max     = 48;
                $left    = '耐受';
                $right   = '敏感';
                break;
            case '3':
                $key    = 'pigment';
                $max    = 29;
                $left   = '非色素';
                $right  = '色素';
                break;
            case '4':
                $key    = 'compact';
                $max    = 51;
                $left   = '紧致';
                $right  = '皱纹';
                break;  
            default:
                $key    = 'dry';
                $max    = 40;
                $left   = '干性';
                $right  = '油性';
                break;
        }
        $userInfo = User::findOne($userId);

        if(!$userInfo){
            throw new NotFoundHttpException("NOT FIND USER");
        }

        $skinInfo   = $skinConfig[$key];

        $answerList = skin::skinTest($type);
        $total      = count($answerList);

        return $this->renderPartial('skin_test.htm',[
            'userId'     => $userId,
            'type'       => $key,
            'from'       => $from,
            'time'       => $time,
            'skinInfo'   => $skinInfo,
            'max'        => $max,
            'left'       => $left,
            'right'      => $right,
            'answerList' => $answerList,
            'total'      => $total,
            'GLOBALS'    => $this->GLOBALS,
        ]);
    }
    /**
     * [actionSkinTest 肤质分享]
     * @return [type] [description]
     */
    public function actionSkinShare(){
        //获取类型
        $userId     = Yii::$app->request->get('user_id');
        $from       = Yii::$app->request->get('from');

        $userId     = intval($userId);
        $time       = time();
        
        $userInfo = User::findOne($userId);
        $skinInfo   = Skin::getUserTestSkin($userId);
        if(!$userInfo || !$skinInfo){
            throw new NotFoundHttpException("NOT FIND USER");
        }
        //增加NUM次数
        foreach ($skinInfo as $key => $value) {
            $skinInfo[$key]['num'] = $key + 1;
        }
        $userInfo   = $userInfo->toArray();
        $userInfo['img'] = Functions::get_image_path($userInfo['img'],1);

        return $this->renderPartial('skin_share.htm',[
            'userInfo'   => $userInfo,
            'skinInfo'   => $skinInfo,
            'from'       => $from,
            'GLOBALS'    => $this->GLOBALS,
        ]);
    }
    /**
     * [actionUserAgreement 用户条款]
     * @return [type] [description]
     */
    public function actionUserAgreement(){
        return $this->renderPartial('agree.htm',[
            'GLOBALS' => $this->GLOBALS,
        ]);
    }
    /**
     * [actionScoreTip 评分细则]
     * @return [type] [description]
     */
    public function actionScoreTip(){
        return $this->renderPartial('score_tip.htm',[
            'GLOBALS' => $this->GLOBALS,
        ]);
    }
    /**
     * [actionQuality 查询说明]
     * @return [type] [description]
     */
    public function actionQuality(){
        return $this->renderPartial('quality_tip.htm',[
            'GLOBALS' => $this->GLOBALS,
        ]);
    }
    public function actionLogin()
    {
        echo 1;
        exit;
    }
    public function actionErrorPage()
    {
        return $this->renderPartial('error.htm',[
            'GLOBALS' => $this->GLOBALS,
        ]);
    }
    /**
     * [actionDownload 下载地址]
     * @return [type] [description]
     */
    public function actionDownload(){
        $url   =  Yii::$app->params['mfrontendUrl'];
        $url   .= Yii::$app->urlManager->createUrl(['site/download-guide']);
        header("Location: $url"); 
    }
    /**
     * [actionDownload 下载地址]
     * @return [type] [description]
     */
    public function actionDownloadFile(){
        $id     = Yii::$app->request->get('id',0);
        $type   = Yii::$app->request->get('type',2);
        $hid    = Yii::$app->request->get('hid',0);
        $url    = Yii::$app->params['downloadUrl'];

        if($this->GLOBALS['equipment'] == 'IOS'){
            $sql = "SELECT downloadUrl FROM {{%app_version}} WHERE status =1 and type=2 ORDER BY  id DESC limit 1";
            $url = Yii::$app->db->createCommand($sql)->queryScalar();

        }else if($this->GLOBALS['equipment'] == 'Android'){
            if($hid){
                $url = Yii::$app->params['frontendUrl'] . 'package/app-huodong-release.apk';
            }else{
                $sql = "SELECT downloadUrl FROM {{%app_version}} WHERE status =1 and type=1 ORDER BY  id DESC limit 1";
                $url = Yii::$app->params['frontendUrl'] . Yii::$app->db->createCommand($sql)->queryScalar();   
            }
        }
        //统计下载数
        Huodong::downLoadCosmetics($hid,$id,$type,'','H5');
        header("Location: $url"); 
    }
    /**
     * [actionUpdateEffect 更新产品功效]
     * @return [type] [description]
     */
    public function actionUpdateEffect(){
        $min        = Yii::$app->request->get('min');
        $max        = Yii::$app->request->get('max');
        $password   = Yii::$app->request->get('password');

        $min        = intval($min);
        $max        = intval($max);
        $max        = $max < $min ? $min : $max;

        if($password == 'tms123'){
            $effect     = [];

            $effectSql = "SELECT effect_id,effect_name FROM {{%product_effect}}";

            $effectArr = Yii::$app->db->createCommand($effectSql)->queryAll();
            foreach ($effectArr as $k => $v) {
                $effect[$v['effect_name']] = $v['effect_id'];
            }
            //查产品
            $compSql    = "SELECT id,product_name FROM {{%product_details}} WHERE id >= '$min' AND id <= '$max'";

            $productArr = Yii::$app->db->createCommand($compSql)->queryAll();

            $n = 0;
            foreach ($productArr as $key => $value) {
                $id     = $value['id'];
                //查成份
                $compSql= "SELECT C.id,C.component_action
                            FROM {{%product_relate}} R LEFT JOIN {{%product_component}}  C ON  R.component_id = C.id
                            WHERE R.product_id = '$value[id]'";
                $componentList = Yii::$app->db->createCommand($compSql)->queryAll();
                //功效成份
                foreach ($componentList as $k => $v) {
                    $component  = $v['component_action'];
                    // $name       = $v['name'];
                    $effectStr  = '';
                    //匹配美白
                    $rule1      = preg_match('/美白祛斑/is', $component); 
                    if($rule1) $effectStr .= $effectStr ? ',1' : '1';
                    //匹配保湿
                    $rule2      = preg_match('/保湿剂/is', $component); 
                    if($rule2) $effectStr .= $effectStr ? ',2' : '2';
                    //匹配舒缓抗敏 
                    $rule3      = preg_match('/舒缓抗敏/is', $component); 
                    if($rule3) $effectStr .= $effectStr ? ',3' : '3';
                    //匹配去角质 
                    $rule4      = preg_match('/去角质/is', $component); 
                    if($rule4) $effectStr .= $effectStr ? ',4' : '4';
                    //匹配去抗皱 
                    $rule5      = preg_match('/抗氧化剂/is', $component); 
                    if($rule5) $effectStr .= $effectStr ? ',5' : '5';
                    //匹配去黑头
                    $rule6      = preg_match('/黑头/is', $value['product_name']); 
                    if($rule6) $effectStr .= $effectStr ? ',6' : '6';
                    //匹配抗痘
                    $rule7      = preg_match('/痘|控油/is', $value['product_name']); 
                    if($rule7) $effectStr .= $effectStr ? ',7' : '7';

                    $sql        = "UPDATE {{%product_details}} SET effect_id = '$effectStr' WHERE id = '$id'";
                    Yii::$app->db->createCommand($sql)->execute();

                    unset($component);
                    // unset($name);
                    unset($effectStr);
                }
                $n ++;
                usleep(100);
            }
            echo '共执行成功'.$n.'条数据';
        }
    }
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
    /**
     * [actionButtonClick 统计点击数]
     * @return [type] [description]
     */
    public function actionButtonClick()
    {
        $id    = Yii::$app->request->get('id');
        $hid   = Yii::$app->request->get('hid',0);
        $type  = Yii::$app->request->get('type',1);
        $id    = intval($id);
        $day   = date('Y-m-d');

        $configArr = [
            '1' => '底部广告',
            '2' => '底部文字',
            '3' => '查询弹窗',
            '4' => '文章轮播',
            '5' => '活动按钮'
        ];

        if(!array_key_exists($id,$configArr)){
            //处理完成，跳转
            $this->redirect(['/site/download-guide','id'=>$id,'hid'=>$hid,'type'=>$type]); 
            // header("Location: http://a.app.qq.com/o/simple.jsp?pkgname=com.miguan.yjy");
            return false;
        }
        $buttonName = $configArr[$id];
        $sql        = "SELECT * FROM {{%log_button_click}}  WHERE  add_time = '$day' AND button_id = '$id'";
        $buttonInfo = Yii::$app->db->createCommand($sql)->queryOne();

        if($buttonInfo) {
            $updateSql  = "UPDATE {{%log_button_click}} SET click_num = click_num + 1 WHERE  add_time = '$day' AND button_id = '$id'";
            
        }else{
            $updateSql  = "INSERT INTO {{%log_button_click}} (button_id,button_name,click_num,add_time) VALUES('$id','$buttonName',1,'$day')";    
        }
        Yii::$app->db->createCommand($updateSql)->execute();  
        //处理完成，跳转
        // header("Location: http://a.app.qq.com/o/simple.jsp?pkgname=com.miguan.yjy");
        $this->redirect(['/site/download-guide','id'=>$id,'hid'=>$hid,'type'=>$type]); 
    }

    /**
     * [actionDownloadGuide 下载引导页]
     * @return [type] [description]
     */
    public function actionDownloadGuide(){
        $id     = Yii::$app->request->get('id',0);
        $hid    = Yii::$app->request->get('hid',0);
        $type   = Yii::$app->request->get('type',1);
        $url    = Yii::$app->urlManager->createUrl(['site/download-file','id'=>$id,'hid'=>$hid,'type'=>$type]);

        $this->GLOBALS['OfficialAccounts']['share']['title']    = '颜究院，一款专业的护肤应用';
        $this->GLOBALS['OfficialAccounts']['share']['desc']     =   '查成分、测肤质，颜究院根据肤质推荐安全有效的护肤品';

        return $this->renderPartial('download.htm',[
            'url'       => $url,
            'GLOBALS'   => $this->GLOBALS,
        ]);

    }
}
