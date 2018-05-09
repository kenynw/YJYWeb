<?php
namespace common\components;

use common\models\ProductBonus;
use Yii;
use common\functions\Functions;
use common\functions\NoticeFunctions;
use common\models\ReportApp;
use common\models\ProductLink;
use common\models\ProductDetails;
use common\models\Article;
use common\models\Brand;
/**
 * 定时任务类
 */
class TimingTaskCom {
	/**
	 * [定时任务，每小时执行一次]
	 */
	static function timingTask1() {
		//在此可以增加定时任务方法
        self::userBan();   //解封用户
	}
    /**
     * [定时任务，每天执行一次]
     */
    static function timingTask2() {
        //在此可以增加定时任务方法
        self::overduePush(); //提醒过期产品
        self::sitemaps();//更新seo网站地图
    }
    /**
     * [定时任务，每小时执行一次]
     */
    static function timingTask3() {
        //在此可以增加定时任务方法
        self::appReport();  //每日报表
    }
    /**
     * [定时任务，每月一号执行]
     */
    static function timingTask4() {
        //在此可以增加定时任务方法
        self::wipeData();
    }

    static function timingTask5() {
        self::updateCoupon();  //获取优惠券
    }
    
	/**
	 * [解封用户]
	*/
    static function userBan(){
        $nowTime    = time();
        $todayTime  = strtotime(date('Y-m-d'));
        $userArr    = [];
        $nowTime    = date('Y-m-d H:i:s');

        $sql        = 'SELECT user_id FROM {{%user_ban}} WHERE expiration_time >= \''.$todayTime.'\' AND expiration_time <= \''.$nowTime.'\''; 
        $userIds    = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($userIds as $key => $value) {
            $userArr[] = $value['user_id'];
        }
        if($userIds){
            $idStr      = Functions::db_create_in($userArr,'id');

            $updateSql  = "UPDATE {{%user}} SET status = '1'  WHERE status != '1' AND ".$idStr;
            Yii::$app->db->createCommand($updateSql)->execute();
        }
        echo  $nowTime."--执行成功\t"; 
    }
    /**
     * [过期推送]
    */
    static function overduePush(){
        //过期时间
        $time       = time();
        $twoMonth   = strtotime(date('Y-m-d',strtotime('+2 month')));
        $oneMonth   = strtotime(date('Y-m-d',strtotime('+1 month')));
        $threeDays  = strtotime(date('Y-m-d',strtotime('+2 day')));
        $nowTime    = date('Y-m-d H:i:s');

        $pushAll    = [
            $twoMonth   => floor(($twoMonth - $time)/(24*3600)),
            $oneMonth   => floor(($oneMonth - $time)/(24*3600)),
            $threeDays  => 1,
        ];

        $sql         = "SELECT id,user_id,product,expire_time FROM {{%user_product}} WHERE expire_time = '$twoMonth' OR expire_time = '$oneMonth' OR expire_time = '$threeDays'"; 
        $productAll  = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($productAll as $key => $value) {
            $expire_time = $value['expire_time'];
            $overdueDay  = $pushAll[$expire_time] + 2;
            if(!array_key_exists($expire_time,$pushAll)) continue;
            $replaceStr = '您的'.$value['product'].'离过期时间还有'. $overdueDay.'天，请及时使用，避免产品变质哦~';

           $return = NoticeFunctions::JPushOne(['Alias' => $value['user_id'],'option' => 'overdueProduct','id'=>$value['id'],'type'=>'1','replaceStr' => $replaceStr]);
            //消息
            NoticeFunctions::notice($value['user_id'], 2 ,$value['id'] );
            usleep(10000);
        }
        echo  $nowTime."--执行成功\t"; 
    }
    /**
     * [APP报表数据]
    */
    static function appReport(){
        $nowDay = date('Y-m-d');
        $day    = Yii::$app->request->get('day');
        $param  = $day ? $day : $nowDay;

        $refererArr =   ['H5','Android','IOS'];
        $date       =   strtotime($param);

        $sql        = "SELECT * FROM {{%report_app}}  WHERE  date = '$date'";
        $reportInfo = Yii::$app->db->createCommand($sql)->queryOne();

        if($reportInfo) {echo  $param."--记录已存在\t";die;}
        

        foreach($refererArr as $referer){
            $referer        = strtolower($referer);
            $userNum        = ReportApp::getUserNum($date,$referer);
            $bannerNum      = ReportApp::getBannerNum($date,$referer);           
            $bannerUserNum  = ReportApp::getBannerUserNum($date,$referer);
            $lessonsNum     = ReportApp::getLessonsNum($date,$referer);
            $skinNum        = ReportApp::getSkinNum($date,$referer); 
            $articleNum     = ReportApp::getArticleInteraction($date,$referer); 
            $productNum     = ReportApp::getProductInteraction($date,$referer); 

            $model      = new ReportApp();
            $model->date                =   $date;
            $model->register_num        =   $userNum;
            $model->banner_click        =   $bannerNum;
            $model->banner_click_num    =   $bannerUserNum;
            $model->lessons_num         =   $lessonsNum;
            $model->evaluating_num      =   $skinNum;
            $model->article_num         =   $articleNum;
            $model->product_num         =   $productNum;
            $model->referer             =   $referer;
            $model->save();

            usleep(1000);
        }
        echo  $param."--生成成功\t"; 
    }
    /**
     * [清空数据]
    */
    static function wipeData(){
        //清空文章表每月点击数
        $nowTime    =   date('Y-m-d H:i:s');
        $updateSql  = "UPDATE {{%article}} SET month_click = '0'";
        Yii::$app->db->createCommand($updateSql)->execute();
        echo  $nowTime."--操作成功\t"; 
    }

    /**
     * [更新淘宝推广链接]
    */
    static function updateLink(){
        //获取商品链接id
        $sql = "SELECT id,goods_id FROM {{%product_bonus}} WHERE is_off = 1";
        $productBonusArr   = Yii::$app->db->createCommand($sql)->queryAll();

        $goodsIdsArr = [];
        if($productBonusArr){
            $goodsIdsArr = array_column($productBonusArr, 'goods_id');
        }

        $goodsIdArr = array_chunk($goodsIdsArr, 40);
        foreach ($goodsIdArr as $goodsId){
            //获取淘宝商品信息
            $data = Functions::getProductLink($goodsId);
            $numIdArr = array_column($data, 'num_iid');
            //更新下架商品
            $diffGoodsIdArr = array_diff($goodsId,$numIdArr);
            $diffGoodsIds = implode($diffGoodsIdArr, ',');

            $updateSql = "UPDATE {{%product_bonus}} SET is_off = 0 WHERE goods_id in ({$diffGoodsIds})";
            Yii::$app->db->createCommand($updateSql)->execute();
            echo "更新成功：{$diffGoodsIds}";
        }
    }

    /**
     * [获取淘宝商品优惠券]
     */
    static function updateCoupon(){
        set_time_limit(0);
        $sql = "SELECT product_id, tb_goods_id, url FROM {{%product_link}} WHERE type = 1 AND url != '' AND status = 1 ORDER BY update_time DESC";
        $productLickArr = Yii::$app->db->createCommand($sql)->queryAll();

        $productLickArr = array_chunk($productLickArr,500);
        foreach ($productLickArr as $productLinks){
            $data = [];
            foreach ($productLinks as $productLink){
                //获取优惠券接口
                $result = Functions::getCoupon($productLink['tb_goods_id']);

                //判断是否授权
                if(isset($result['errorMsg']) && $result['errorMsg'] == "非法或过期的SessionKey参数，请使用有效的SessionKey参数"){
                    return '未授权';
                }

                //是否已有数据
                $sqlOne = "SELECT end_date FROM {{%product_bonus}} WHERE product_id = {$productLink['product_id']}";
                $bonusOne = Yii::$app->db->createCommand($sqlOne)->queryOne();

                //判断淘宝商品是否下架
                if(isset($result['errorMsg']) && $result['errorMsg'] == "该item_id对应宝贝已下架或非淘客宝贝" && $bonusOne){
                    $updateSql = "UPDATE {{%product_bonus}} SET is_off = 0 WHERE goods_id = {$productLink['tb_goods_id']}";
                    Yii::$app->db->createCommand($updateSql)->execute();

                    $updateLinkSql = "UPDATE {{%product_link}} SET status = 0 WHERE tb_goods_id = {$productLink['tb_goods_id']}";
                    Yii::$app->db->createCommand($updateLinkSql)->execute();
                    echo "更新成功：{$productLink['tb_goods_id']}";
                    continue;
                }

                //判断淘宝商品是否有优惠券
                $one = [];
                if(isset($result['tbLink']) && $result['remainCount'] > 0){
                    //获取优惠券价格
                    $price = [];
                    preg_match_all('/(\d+)/i', $result['info'], $price);
                    $couponPrice = count($price[0]) == 1 ? $price[0][0] : $price[0][1];

                    if($bonusOne){
                        if($bonusOne['end_date'] != $result['endTime']){
                            $time = time();
                            $updateSql = "UPDATE {{%product_bonus}} SET bonus_link = '{$result['tbLink']}', price = {$couponPrice},
                                  start_date = '{$result['startTime']}', end_date = '{$result['endTime']}', updated_at = {$time}
                                  WHERE product_id = {$productLink['product_id']}";
                            Yii::$app->db->createCommand($updateSql)->execute();
                            echo '更新优惠券数据:'.$productLink['product_id'];
                        }
                    }else{
                        $one[] = $productLink['product_id'];
                        $one[] = $productLink['tb_goods_id'];
                        $one[] = $productLink['url'];
                        $one[] = $result['tbLink'];
                        $one[] = $couponPrice;
                        $one[] = $result['startTime'];
                        $one[] = $result['endTime'];
                        $one[] = 1;
                        $one[] = time();
                        $one[] = time();

                        $data[] = $one;
                    }
                }
                sleep(1);
            }

            //批量插入
            if(count($data) > 0){
                $command = Yii::$app->db->createCommand();
                $command->batchInsert("{{%product_bonus}}",
                    ['product_id', 'goods_id', 'goods_link', 'bonus_link', 'price', 'start_date', 'end_date', 'status', 'created_at', 'updated_at'],
                    $data)->execute();
                echo '插入优惠券数据';
            }

            sleep(1800);
        }
    }
    
    /**
     * [更新sitemaps]
     */
    static function sitemaps(){
        //产品详情页
        $pageSize = 1000;
        $pageMin = 0;
        $txtPageSize = 50000;
        $count = ProductDetails::find()->count();
        $total = intval(ceil($count/$pageSize));
        $txtTotal = intval(ceil($count/$pageSize));
        $all = 0;
        $content = '';
        $j = 1;
        
        for($i=0;$i<$total;$i++) {
            $pageMin = $i * $pageSize;
            $sql = "SELECT id FROM {{%product_details}} limit $pageMin,$pageSize";
            $idArr = Yii::$app->db->createCommand($sql)->queryColumn();
    
            if (!empty($idArr)) {
                foreach ($idArr as $key=>$val) {
                    $content .= "https://www.yjyapp.com/product/".$val.".html\r\n";
                }
    
                $all = ($i+1) * $pageSize;

                if ($all%$txtPageSize == 0 || $i +1  == $total) {
                    $j = $j+1;
                    $url = dirname(yii::$app->basePath)."/frontend/web/sitemap"."$j".".txt";
                    file_put_contents($url,$content);
                    $content = '';
                }
    
                usleep(100);
            }
        }
        
        //文章详情、品牌详情
        $count = Article::find()->count();
        $total = intval(ceil($count/$pageSize));
        $content = '';
        
        for($i=0;$i<=$total;$i++) {
            $pageMin = $i * $pageSize;
            $sql = "SELECT id FROM {{%article}} limit $pageMin,$pageSize";
            $idArr = Yii::$app->db->createCommand($sql)->queryColumn();
        
            if (!empty($idArr)) {
                foreach ($idArr as $key=>$val) {
                    $content .= "https://www.yjyapp.com/article/".$val.".html\r\n";
                }
        
                usleep(100);
            }
        }
        
        $count = Brand::find()->count();
        $total = intval(ceil($count/$pageSize));
        
        for($i=0;$i<=$total;$i++) {
            $pageMin = $i * $pageSize;
            $sql = "SELECT id FROM {{%brand}} limit $pageMin,$pageSize";
            $idArr = Yii::$app->db->createCommand($sql)->queryColumn();
        
            if (!empty($idArr)) {
                foreach ($idArr as $key=>$val) {
                    $content .= "https://www.yjyapp.com/brand/".$val.".html\r\n";
                }
        
                usleep(100);
            }
        }
        
        $url = dirname(yii::$app->basePath)."/frontend/web/sitemap1.txt";
        file_put_contents($url,$content);
    }
}
?>