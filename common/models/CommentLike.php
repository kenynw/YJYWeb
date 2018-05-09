<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%comment_like}}".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $type
 * @property string $post_id
 * @property string $comment_id
 * @property string $created_at
 * @property string $updated_at
 */
class CommentLike extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%comment_like}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'post_id', 'comment_id', 'created_at', 'updated_at'], 'integer'],
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

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户id',
            'type' => '类型 1-产品，2-文章',
            'post_id' => '帖子id',
            'comment_id' => '评论id',
            'created_at' => '点赞时间',
            'updated_at' => '更新时间',
        ];
    }
}
