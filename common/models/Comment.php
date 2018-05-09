<?php

namespace common\models;

use Yii;
use yii\helpers\Html;
use common\functions\Tools;

class Comment extends \yii\db\ActiveRecord
{
    public $product_author;
    public $article_author;
    public $img;
    public $desc_time;
    public $comment_all;
    public $comment_num;

    public static function tableName()
    {
        return '{{%comment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id', 'user_id','comment','type','star'], 'required'],
            [['first_id', 'parent_id', 'type', 'post_id', 'user_id', 'admin_id', 'user_type', 'star', 'like_num', 'is_reply','is_digest','is_read', 'status', 'created_at', 'updated_at'], 'integer'],
            [['author','img'], 'string', 'max' => 255],
            [['comment'], 'string', 'max' => 1000],
            [['comment'], 'trim'],
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if($insert) {
                $this->created_at = time();
                $this->updated_at = time();
            }else{
                $this->updated_at = time();
            }
            return true;
        } else {
            return false;
        }
    }
    
    public static function getPostId($type,$postId) {
        if ($type == '1') {
            $model = ProductDetails::findOne($postId);
            $return = empty($model) ? '' : $model->product_name;
        } elseif ($type == '2') {
            $model = Article::findOne($postId);
            $return = empty($model) ? '' : $model->title;
        } elseif ($type == '3') {
            $model = Video::findOne($postId);
            $return = empty($model) ? '' : $model->title;
        }      

        return $return;
    }
    
    public static function getImg($id,$type = '1') {
        $img = Attachment::find()->select("attachment")->where("cid = $id AND type = $type")->orderBy("aid")->asArray()->column();
        if (!empty($img)) {
            $return = serialize($img);
        } else {
            $return = '';
        }

        return $return;
    }
    
    public static function getSecondCommentNum($id) {
        $num = Comment::find()->where("first_id = $id AND status = 1")->count();
    
        return  $num;
    }
    
    public static function getCommentList($id) {
        //一级
        $model = Comment::findOne($id);
        $firstId = $model->first_id;
        $firstComment = Comment::findOne($firstId);
        $img = empty(self::getImg($firstId)) ? '' : '&nbsp;'.Html::a('查看图片','javascript:void(0)',['data-url' => Yii::$app->params['uploadsUrl'],'data-src' => self::getImg($firstId),'class' => 'comment-img','data-toggle' => 'modal','data-target' => "#comment-img"]);
        $firstColor = $firstComment->admin_id == 0 ? 'green' : 'blue';
        
        //二级
        $sql = "SELECT * FROM {{%comment}} WHERE first_id=$firstId AND status = 1 ORDER BY created_at";
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

        $str = '';
        $str .= '<div style="width:100%;margin-top:10px;" id="comment-box">
                <div style="width:100%;"><a href="/user/view?id='.$firstComment->user_id.'" target="_blank"><span style="color:'.$firstColor.'">'.Tools::userTextDecode($firstComment->author).'</span></a><br>'.Tools::userTextDecode($firstComment->comment).$img.'</div><hr style="height:1px;border:none;border-top:1px solid rgb(212, 208, 208);margin-top:2px;margin-bottom:5px;" />';
        foreach ($comment as $key=>$val) {
            $color1 = $val['admin_id']  == 0 ? 'green' : 'blue';
            $color2 = $val['parent_admin_id'] == 0 ? 'green' : 'blue';
            $author2 = $val['first_id'] == $val['parent_id'] ? '：' : '回复 <a href="/user/view?id='.$val['parent_user_id'].'" target="_blank"><span style="color:'.$color2.'">'.$val['parent_username'].'</span></a>：';
            
            $str .= '<div style="width:100%;padding:0 0 5px 0;"><a href="/user/view?id='.$val['user_id'].'" target="_blank"><span style="color:'.$color1.'">'.$val['author'].'</span></a> '.$author2.$val['comment'].'</div>';
        }
        $str .= '</div>';
        
        return  $str;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '评论id',
            'first_id' => '一级评论id',
            'parent_id' => '父级id',
            'type' => '类型 1-产品，2-文章',
            'post_id' => '帖子id',
            'user_id' => '用户id',
            'admin_id' => '管理员id',
            'user_type' => '用户类型，0真实用户，1马甲',
            'author' => '评论人',
            'star' => '星级',
            'comment' => '评论信息',
            'like_num' => '点赞数',
            'is_reply' => '0为未回复，1回复',
            'is_digest' => '状态',
            'status' => '是否删除',
            'img' => '图片',
            'created_at' => '评论时间',
            'updated_at' => '更新时间',
        ];
    }
}
