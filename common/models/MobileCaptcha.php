<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%mobile_captcha}}".
 *
 * @property string $id
 * @property string $mobile
 * @property string $captcha
 * @property string $is_use
 * @property string $using_time
 * @property string $expire_time
 * @property string $created_at
 * @property string $updated_at
 */
class MobileCaptcha extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%mobile_captcha}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile', 'captcha', 'is_use', 'using_time', 'expire_time', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                //'value' => new Expression('NOW()'),
                //'value'=>$this->timeTemp(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mobile' => 'Mobile',
            'captcha' => 'Captcha',
            'is_use' => 'Is Use',
            'using_time' => 'Using Time',
            'expire_time' => 'Expire Time',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
