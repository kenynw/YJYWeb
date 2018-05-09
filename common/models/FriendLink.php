<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%friend_link}}".
 *
 * @property integer $link_id
 * @property string $link_name
 * @property string $link_url
 * @property string $link_logo
 * @property integer $show_order
 * @property integer $show_type
 * @property string $add_time
 */
class FriendLink extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%friend_link}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['link_name', 'link_url'], 'required'],
            [['show_order', 'show_type'], 'integer'],
            [['add_time'], 'safe'],
            [['link_name', 'link_url', 'link_logo'], 'string', 'max' => 255],
            [['link_url'],'url', 'defaultScheme' => 'http','message'=>'地址不是有效格式'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'link_id' => 'ID',
            'link_name' => '友链名',
            'link_url' => '友链地址',
            'link_logo' => 'Link Logo',
            'show_order' => 'Show Order',
            'show_type' => 'Show Type',
            'add_time' => 'Add Time',
        ];
    }
}
