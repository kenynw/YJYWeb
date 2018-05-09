<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%topic}}".
 *
 * @property string $id
 * @property string $title
 * @property string $desc
 * @property string $picture
 * @property integer $sort
 * @property string $post_num
 * @property string $comment_num
 * @property integer $status
 * @property string $created_at
 */
class Topic extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%topic}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'desc','picture','share_pic'], 'required'],
            [['sort', 'post_num', 'comment_num', 'status'], 'integer'],
            [['created_at'], 'safe'],
            [['title', 'desc', 'picture','share_pic'], 'string', 'max' => 255],
            [['title', 'desc'], 'trim'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'title' => '话题名称',
            'desc' => '话题简介',
            'picture' => 'banner图',
            'share_pic' => '分享图',
            'sort' => 'Sort',
            'post_num' => '帖子数',
            'comment_num' => 'Comment Num',
            'status' => '状态',
            'created_at' => '创建时间',
        ];
    }
}
