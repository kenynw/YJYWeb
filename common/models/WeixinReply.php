<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%weixin_reply}}".
 *
 * @property string $id
 * @property string $keyword
 * @property string $reply
 * @property string $add_time
 */
class WeixinReply extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%weixin_reply}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['keyword','reply'], 'required'],
            [['add_time'], 'safe'],
            [['type'], 'integer'],
            [['keyword','match_mode'], 'string', 'max' => 255],
            [['reply'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'keyword' => '关键词',
            'reply' => '回复内容',
            'add_time' => '添加时间',
            'match_mode' => '匹配模式',
            'type' => '类型'
        ];
    }
}
