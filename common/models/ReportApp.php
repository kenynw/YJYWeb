<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%report_app}}".
 *
 * @property integer $id
 * @property integer $register_num
 * @property integer $banner_click
 * @property integer $banner_click_num
 * @property integer $lessons_num
 * @property integer $evaluating_num
 * @property integer $article_num
 * @property integer $product_num
 * @property string $referer
 * @property integer $date
 */
class ReportApp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%report_app}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['register_num', 'banner_click', 'banner_click_num', 'lessons_num', 'evaluating_num', 'article_num', 'product_num', 'date'], 'integer'],
            [['evaluating_num', 'article_num', 'product_num', 'date'], 'required'],
            [['referer'], 'string', 'max' => 10],
        ];
    }
    /**
     * [getUserNum 统计注册数]
     * @param  [type] $date    [description]
     * @param  string $referer [description]
     * @return [type]          [description]
     */
    public static function getUserNum($date,$referer = '')
    {
        $endtime  = $date + 86399;
        $whereStr = "created_at >= '$date' AND created_at <= '$endtime' AND admin_id = '0'";
        $referer ? $whereStr .= " AND referer = '$referer' " : '';
        $sql     = "SELECT COUNT(*) FROM {{%user}} WHERE $whereStr";
        $num     = Yii::$app->db->createCommand($sql)->queryScalar();
        return $num;
    }
    /**
     * [getShareNum 统计轮播点击数]
     * @param  [type] $date    [description]
     * @param  string $referer [description]
     * @return [type]          [description]
     */
    public static function getBannerNum($date,$referer = '')
    {
        $endtime  = $date + 86399;

        $whereStr = "created_at >= '$date' AND created_at <= '$endtime'";
        $referer ? $whereStr .= " AND referer = '$referer' " : '';

        $sql  = "SELECT COUNT(*) FROM {{%log_banner}} WHERE $whereStr";
        $num  = Yii::$app->db->createCommand($sql)->queryScalar(); 

        return $num;
    }
    /**
     * [getShareNum 统计轮播点击人数]
     * @param  [type] $date    [description]
     * @param  string $referer [description]
     * @return [type]          [description]
     */
    public static function getBannerUserNum($date,$referer = '')
    {
        $endtime  = $date + 86399;

        $whereStr = "created_at >= '$date' AND created_at <= '$endtime'";
        $referer ? $whereStr .= " AND referer = '$referer' " : '';

        $sql  = "SELECT COUNT(*) FROM (SELECT * FROM {{%log_banner}} WHERE $whereStr GROUP BY user_id) AS B";
        $num  = Yii::$app->db->createCommand($sql)->queryScalar(); 

        return $num;
    }
    /**
     * [getLessonsNum 统计功课参与人数]
     * @param  [type] $date    [description]
     * @param  string $referer [description]
     * @return [type]          [description]
     */
    public static function getLessonsNum($date,$referer = '')
    {
        $endtime  = $date + 86399;

        $whereStr = "U.admin_id = '0' AND L.created_at >= '$date' AND L.created_at <= '$endtime'";
        $referer ? $whereStr .= " AND L.referer = '$referer' " : '';

        $sql = "SELECT COUNT(*) FROM {{%log_lessons}} L LEFT JOIN {{%user}} U ON U.id = L.user_id WHERE $whereStr";
        $num = Yii::$app->db->createCommand($sql)->queryScalar(); 

        return $num;
    }
    /**
     * [getSkinNum 肤质评测人数]
     * @param  [type] $date    [description]
     * @param  string $referer [description]
     * @return [type]          [description]
     */
    public static function getSkinNum($date,$referer = '')
    {
        $endtime  = $date + 86399;

        $whereStr = "U.admin_id = '0' AND  L.created_at >= '$date' AND L.created_at <= '$endtime'";
        $referer ? $whereStr .= " AND L.referer = '$referer' " : '';

        $sql = "SELECT COUNT(*) FROM ( SELECT L.id FROM  {{%log_skin}} L LEFT JOIN {{%user}} U ON U.id = L.user_id WHERE $whereStr GROUP BY L.user_id) AS S";
        $num = Yii::$app->db->createCommand($sql)->queryScalar(); 

        return $num;
    }
    /**
     * [getShareNum 文章互动数]
     * @param  [type] $date    [description]
     * @param  string $referer [description]
     * @return [type]          [description]
     */
    public static function getArticleInteraction($date,$referer = '')
    {
        $startTime  = $date;
        $endTime    = $date + 86399;

        $whereStr = "L.type = 1 AND U.admin_id = '0' AND L.created_at >= '$startTime' AND L.created_at <= '$endTime'";
        $referer ? $whereStr .= " AND L.referer = '$referer' " : '';
        //分享数
        $shareSql = "SELECT COUNT(*) FROM {{%log_share}} L LEFT JOIN {{%user}} U ON U.id = L.user_id WHERE $whereStr";
        $shareNum = Yii::$app->db->createCommand($shareSql)->queryScalar(); 
        //点赞数
        $likeSql  = "SELECT COUNT(*) FROM {{%comment_like}} L LEFT JOIN {{%user}} U ON U.id = L.user_id WHERE $whereStr";
        $likeNum  = Yii::$app->db->createCommand($likeSql)->queryScalar(); 
        //评论数
        $commentSql = "SELECT COUNT(*) FROM {{%comment}} L LEFT JOIN {{%user}} U ON U.id = L.user_id WHERE $whereStr";
        $commentNum = Yii::$app->db->createCommand($commentSql)->queryScalar(); 

        return $shareNum + $likeNum + $commentNum;
    }
    /**
     * [getShareNum 产品互动数]
     * @param  [type] $date    [description]
     * @param  string $referer [description]
     * @return [type]          [description]
     */
    public static function getProductInteraction($date,$referer = '')
    {
        $endtime  = $date + 86399;

        $whereStr = "U.admin_id = '0' AND  L.type = 2 AND L.created_at >= '$date' AND L.created_at <= '$endtime'";
        $referer ? $whereStr .= " AND L.referer = '$referer' " : '';
        //分享数
        $shareSql = "SELECT COUNT(*) FROM {{%log_share}} L LEFT JOIN {{%user}} U ON U.id = L.user_id WHERE $whereStr";
        $shareNum = Yii::$app->db->createCommand($shareSql)->queryScalar(); 
        //点赞数
        $likeSql  = "SELECT COUNT(*) FROM {{%comment_like}} L LEFT JOIN {{%user}} U ON U.id = L.user_id WHERE $whereStr";
        $likeNum  = Yii::$app->db->createCommand($likeSql)->queryScalar(); 
        //评论数
        $commentSql = "SELECT COUNT(*) FROM {{%comment}} L LEFT JOIN {{%user}} U ON U.id = L.user_id WHERE $whereStr";
        $commentNum = Yii::$app->db->createCommand($commentSql)->queryScalar(); 

        return $shareNum + $likeNum + $commentNum;
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'register_num' => '注册数',
            'banner_click' => 'banner点击次数',
            'banner_click_num' => 'banner点击人数',
            'lessons_num' => '功课生成数',
            'evaluating_num' => '肤质评测参与人数',
            'article_num' => '文章互动数（点赞+评论+分享）',
            'product_num' => '产品互动数（点赞+评论+分享）',
            'referer' => '来源',
            'date' => '日期',
        ];
    }
}
