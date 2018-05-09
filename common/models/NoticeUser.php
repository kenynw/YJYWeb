<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tms_notice_user".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $type
 * @property string $content
 * @property integer $status
 * @property string $created_at
 */
class NoticeUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%notice_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'status', 'created_at','relation_id'], 'integer'],
            [['content'], 'string', 'max' => 255],
        ];
    }
    
    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
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
            'user_id' => 'User ID',
            'type' => 'Type',
            'content' => 'Content',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }
}
