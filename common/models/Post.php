<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%post}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $topic_id
 * @property string $content
 * @property string $views_num
 * @property string $like_num
 * @property string $comment_num
 * @property integer $status
 * @property string $created_at
 */
class Post extends \yii\db\ActiveRecord
{
    public $img;
    public $topic_title;
    public $user_type;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%post}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'topic_id', 'views_num', 'like_num', 'comment_num', 'status','user_type'], 'integer'],
            [['user_id', 'topic_id','content'], 'required'],
            [['created_at'], 'safe'],
            [['ratio'], 'number'],
            [['img','topic_title','picture'], 'string', 'max' => 255],
            [['content'], 'string', 'max' => 5000],
            [['content'], 'trim'],
        ];
    }
    
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    public function getTopic()
    {
        return $this->hasOne(Topic::className(), ['id' => 'topic_id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'user_id' => '用户名',
            'topic_id' => '话题名称',
            'content' => '帖子内容',
            'views_num' => 'Views Num',
            'like_num' => '点赞数',
            'comment_num' => '评论数',
            'status' => 'Status',
            'created_at' => '发布时间',
            'img' => '图片',
            'topic_title' => '话题名称',
            'user_type' => '用户名'
        ];
    }
}
