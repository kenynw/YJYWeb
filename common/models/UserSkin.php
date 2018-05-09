<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_skin}}".
 *
 * @property string $uid
 * @property integer $dry
 * @property integer $tolerance
 * @property integer $pigment
 * @property integer $compact
 * @property string $skin_name
 * @property string $add_time
 */
class UserSkin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_skin}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'skin_name'], 'required'],
            [['uid', 'dry', 'tolerance', 'pigment', 'compact', 'add_time'], 'integer'],
            [['skin_name'], 'string', 'max' => 8],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => 'Uid',
            'dry' => 'Dry',
            'tolerance' => 'Tolerance',
            'pigment' => 'Pigment',
            'compact' => 'Compact',
            'skin_name' => 'Skin Name',
            'add_time' => 'Add Time',
        ];
    }
}
