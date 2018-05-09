<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%ask_reply}}".
 *
 * @property string $replyid
 * @property string $askid
 * @property string $reply
 * @property string $username
 * @property string $user_id
 * @property string $add_time
 */
class AskReply extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ask_reply}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['askid', 'user_id', 'add_time','admin_id'], 'integer'],
            [['username','reply'], 'required'],            
            [['reply', 'username','picture'], 'string', 'max' => 255],
            [['reply'], 'trim'],
            
        ];
    }
    
    public function getAsk()
    {
        return $this->hasOne(Ask::className(), ['askid' => 'askid']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'replyid' => 'Replyid',
            'askid' => '问题ID',
            'reply' => '回复内容',
            'username' => '用户名',
            'user_id' => '用户ID',
            'picture' => '图片',
            'add_time' => '回复时间',
        ];
    }
}
