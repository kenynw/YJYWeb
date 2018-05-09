<?php

namespace m\controllers;

use Yii;
use yii\web\Controller;
use m\models\WebPage;
use yii\web\NotFoundHttpException;
use common\functions\Functions;
use common\functions\Skin;
use common\functions\Tools;
use common\functions\ApiFunctions;
/**
 * App controller
 */
class AppController extends BaseController
{

    /**
     * [actionIndex 排行榜分享落地页]
     * @return [type] [description]
     */
    public function actionRanking()
    {
        $id  = Yii::$app->request->get('id');
        $id  = intval($id);

        if(!$id) throw new NotFoundHttpException("NOT FIND RANKING");

        //查询排行榜信息
        $rankingInfo    =  Functions::getRankingInfo($id);
        if(!$rankingInfo) throw new NotFoundHttpException("NOT FIND RANKING");

        //关联排行榜
        $rankingRelation = Functions::getRankingRelation($id,'5');

        return $this->renderPartial('ranking.htm', [
            'rankingInfo'       => $rankingInfo,
            'rankingRelation'   => $rankingRelation,
            'GLOBALS'           => $this->GLOBALS,
        ]);
    }
    /**
     * [actionBaike 百科]
     * @return [type] [description]
     */
    public function actionBaike()
    {
        $id  = Yii::$app->request->get('id');
        $id  = intval($id);

        if(!$id) throw new NotFoundHttpException("NOT FIND BAIKE");

        //查询百科信息
        $baikeInfo    =  Functions::getBaikeInfo($id);

        if(!$baikeInfo) throw new NotFoundHttpException("NOT FIND BAIKE");

        //相关问题
        $baikeRelation= Functions::getbaikeRelation($id,'3');

        return $this->renderPartial('baike.htm', [
            'baikeInfo'       => $baikeInfo,
            'baikeRelation'   => $baikeRelation,
            'GLOBALS'         => $this->GLOBALS,
        ]);
    }
    /**
     * [actionBaike 临时更正时间]
     * @return [type] [description]
     */
    public function actionUpdateTime()
    {
        $uid      = Yii::$app->request->get('uid','');
        $password = Yii::$app->request->get('password','');

        if($password != 'tmshuo123') throw new NotFoundHttpException("NOT FIND ACTION");

        $whereStr = ' 1 = 1 ';
        $whereStr.= $uid ? " AND user_id = '$uid' " :'';

        $sql   = "SELECT * FROM {{%user_product}} WHERE $whereStr";
        $data  = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($data as $key => $value) {
            $id = $value['id'];
            if($value['is_seal'] == 1){
                $month       = $value['quality_time'];
                $expire_time = $month ? strtotime(date('Y-m-d',strtotime("+$month month",$value['seal_time']))) : $value['overdue_time'];
                $expire_time = $expire_time > $value['overdue_time'] ? $value['overdue_time'] : $expire_time;
                unset($month);
            }else{
                $expire_time = $value['overdue_time'];
            }
            $updateSql = "UPDATE {{%user_product}} SET expire_time = '$expire_time' WHERE id = '$id'";
            Yii::$app->db->createCommand($updateSql)->execute();
            unset($expire_time,$id);
            usleep(100);
        }
        return true;
    }
    /**
     * [actionSwoole 聊天室]
     * @return [type] [description]
     */
    public function actionSwoole()
    {
        return $this->renderPartial('swoole.htm', [
            'GLOBALS'         => $this->GLOBALS,
        ]);
    }
    /**
     * [actionVideo 视频分享]
     * @return [type] [description]
     */
    public function actionVideo()
    {
        $id    = Yii::$app->request->get('id');
        $id    = intval($id);
        $rows  = [];

        if(!$id) throw new NotFoundHttpException("NOT FIND VIDEO");

        //查询信息
        $videoInfo  =  Functions::getVideosInfo($id);

        if(!$videoInfo) throw new NotFoundHttpException("NOT FIND VIDEO");
        //评论
        $whereStr   = " C.type = '3' AND C.first_id = '0' AND C.post_id = '$id' AND C.status = '1' ";
        $fieldStr   = 'C.is_digest DESC,C.like_num DESC ';
        $sql        = "SELECT C.id,C.user_id,C.comment,C.like_num,C.is_digest,S.dry,S.tolerance,S.pigment,S.compact FROM {{%comment}} C 
                       LEFT JOIN {{%user_skin}} S ON C.user_id = S.uid
                       WHERE $whereStr ORDER BY  $fieldStr ,C.created_at DESC";
        $commentList   = Yii::$app->db->createCommand($sql)->queryAll();

        if(!empty($commentList)){
            foreach ($commentList as $key => $value) {
                $commentList[$key]['comment'] = Tools::userTextDecode($value['comment']);
                $rows[$key]    = Functions::getCommentInfo($value['id']);
                $reply         = Functions::getCommentReply($value['id']);
                if($reply) $rows[$key]['reply'] =  $reply;
                unset($reply);
            }
            foreach ($rows as $key => $value) {
                if($value['user']['skin']){
                    $rows[$key]['user']['skin'] = explode(' | ',$value['user']['skin']);
                }
            }
        }

        return $this->renderPartial('video.htm', [
            'videoInfo'       => $videoInfo,
            'commentList'     => $rows,
            'GLOBALS'         => $this->GLOBALS,
        ]);
    }
    /**
     * [actionTopicInfo 话题页面]
     * @return [type] [description]
     */
    public function actionTopicInfo()
    {
        $id  = Yii::$app->request->get('id');
        $id  = intval($id);

        $topicInfo = Functions::getTopicInfo($id);

        if(!$topicInfo) {
            throw new NotFoundHttpException("NOT FIND TOPIC");
        }
        //查询
        $params     = ['id' => $id];
        $postList   = ApiFunctions::postList($params);

        return $this->renderPartial('topic.htm', [
            'topicInfo'  => $topicInfo,
            'postList'   => $postList,
            'GLOBALS'    => $this->GLOBALS,
        ]);
    }
    /**
     * [actionTopicInfo 话题帖页面]
     * @return [type] [description]
     */
    public function actionPostInfo()
    {
        $id  = Yii::$app->request->get('id');
        $id  = intval($id);

        $postInfo   = Functions::getPostInfo($id);

        if(!$postInfo) {
            throw new NotFoundHttpException("NOT FIND POST");
        }
        //评论
        $whereStr   = " C.type = '4' AND C.first_id = '0' AND C.post_id = '$id' AND C.status = '1' ";
        $fieldStr   = 'C.is_digest DESC,C.like_num DESC ';
        $sql        = "SELECT C.id,C.user_id,C.comment,C.like_num,C.is_digest,S.dry,S.tolerance,S.pigment,S.compact FROM {{%comment}} C 
                       LEFT JOIN {{%user_skin}} S ON C.user_id = S.uid
                       WHERE $whereStr ORDER BY  $fieldStr ,C.created_at DESC";
        $commentList   = Yii::$app->db->createCommand($sql)->queryAll();

        if(!empty($commentList)){
            foreach ($commentList as $key => $value) {
                $commentList[$key]['comment'] = Tools::userTextDecode($value['comment']);
                $commentList[$key]            = Functions::getCommentInfo($value['id']);
                $reply                        = Functions::getCommentReply($value['id']);
                $commentList[$key]['user']    = Functions::getUserInfo($value['user_id']);
                $commentList[$key]['reply']   = '';
                $commentList[$key]['reply']   =  $reply;
                unset($reply);
            }
        }

        return $this->renderPartial('post.htm', [
            'postInfo'      => $postInfo,
            'commentList'   => $commentList,
            'GLOBALS'       => $this->GLOBALS,
        ]);
    }
    /**
     * [historical 历史价格]
     * @return [type] [description]
     */
    public function actionHistorical(){
        //获取商品ID
        $id     = Yii::$app->request->get('id');
        $id     = (float)$id;
        $date   = date('Y-m-d');

        if(!$id) {
            throw new NotFoundHttpException("Unfilled ID");
        }
        $data = [];
        $messageArr = [];
        $list = [];

        $couponInfo = Functions::getCouponInfo($id);

        $cache          =    Yii::$app->cache;
        $results        =    $cache->get('m_bonus_rand_goods');
        if(!$results){
            //获取商品列表
            $productSql = " SELECT B.id,B.goods_id,B.goods_link,B.bonus_link,B.price
                            FROM {{%product_bonus}} AS B
                            LEFT JOIN {{%product_details}} P ON P.id = B.product_id 
                            WHERE B.status = 1 AND B.is_off = 1 AND  B.end_date >= '{$date}'
                            ORDER BY B.price DESC LIMIT 30";
            $productBonusArr   = Yii::$app->db->createCommand($productSql)->queryAll();

            foreach ($productBonusArr as $productBonus){
                $goodsId    = $productBonus['goods_id'];
                $data[]     = $goodsId;
                $messageArr[$goodsId] = [
                    'bonus_link'    =>  $productBonus['bonus_link'],
                    'goods_link'    =>  $productBonus['goods_link'],
                    'price'         =>  $productBonus['price']
                ];
                unset($goodsId);
            }

            //从淘宝接口中获取商品信息
            $results = $data ? Functions::getProductLink($data) : [];
            if($results){
                foreach ($results as $key=>$result){
                    $goodsId = $result['num_iid'];
                    $results[$key]['link']  = $messageArr["{$goodsId}"]['bonus_link'];
                    $results[$key]['price'] = intval($messageArr["{$goodsId}"]['price']);

                    $results[$key]['voucherPrice'] = floatval($results[$key]['zk_final_price'] - $messageArr["{$goodsId}"]['price']);
                    unset($goodsId);
                }
            }
            $cache->set('m_bonus_rand_goods',$results,1800);
        }

        if($results){
            shuffle($results);
            $list       =  array_slice($results,0,5); 
            $list       =  Functions::array_sort($list,'price',SORT_DESC);
        }

        return $this->renderPartial('historical.htm', [
            'id'            => $id,
            'couponInfo'    => $couponInfo,
            'list'          => $list,
            'GLOBALS'       => $this->GLOBALS,
        ]);
    }
    /**
     * [historical 历史价格]
     * @return [type] [description]
     */
    public function actionHistoricalGuide(){
        //获取商品ID
        $id  = Yii::$app->request->get('id');
        $id  = (float)$id;

        if(!$id) {
            throw new NotFoundHttpException("Unfilled ID");
        }
        if (!strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            //存在则查询高返链接
            $couponInfo = Functions::getCoupon($id);
            if($couponInfo) {
                $url = $couponInfo['tbLink'];
                $this->redirect($url);
            }
        }
        return $this->renderPartial('historical_guide.htm', [
            'GLOBALS'       => $this->GLOBALS,
        ]);
    }
}
