<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%huodong_draw_log}}".
 *
 * @property string $id
 * @property integer $hid
 * @property string $user_id
 * @property string $relation_id
 * @property string $referer
 * @property string $ext
 * @property string $add_time
 */
class HuodongDrawLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public $num;
    public static function tableName()
    {
        return '{{%huodong_draw_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['hid', 'user_id', 'relation_id','num'], 'integer'],
            [['add_time'], 'safe'],
            [['referer', 'ext'], 'string', 'max' => 255],
        ];
    }
    
    public function getHuodongSpecialDraw()
    {
        return $this->hasOne(HuodongSpecialDraw::className(), ['id' => 'relation_id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'hid' => 'Hid',
            'user_id' => 'User ID',
            'relation_id' => 'Relation ID',
            'referer' => 'Referer',
            'ext' => 'Ext',
            'add_time' => 'Add Time',
        ];
    }
}
