<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%post_like}}".
 *
 * @property string $id
 * @property string $post_id
 * @property string $topic_id
 * @property string $user_id
 * @property string $referer
 * @property string $add_time
 */
class PostLike extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%post_like}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id', 'topic_id', 'user_id'], 'integer'],
            [['add_time'], 'safe'],
            [['referer'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'post_id' => 'Post ID',
            'topic_id' => 'Topic ID',
            'user_id' => 'User ID',
            'referer' => 'Referer',
            'add_time' => 'Add Time',
        ];
    }
}
