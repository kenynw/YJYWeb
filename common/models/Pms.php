<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
/**
 * This is the model class for table "yjy_pms".
 *
 * @property integer  $id
 * @property integer $type
 * @property integer $relation_id
 * @property integer $relation_type
 * @property integer $from_id
 * @property integer $receive_id
 * @property string $message
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 */
class Pms extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pms}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type','relation_id', 'from_id', 'receive_id', 'status', 'created_at', 'updated_at','log_id'], 'integer'],
            [['message'], 'string'],
            [['receive_id','log_id'], 'default', 'value' => 0],
        ];
    }
    
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }
    
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'from_id']);
    }
    

    public function getAdmin()
    {
        return $this->hasOne(Admin::className(), ['id' => 'from_id']);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'relation_id' => 'RELATION_ID',
            'from_id' => 'From ID',
            'receive_id' => 'Receive ID',
            'message' => 'Message',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
