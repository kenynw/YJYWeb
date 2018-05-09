<?php

namespace common\models;

use Yii;
use backend\models\CommonFun;
use yii\base\Object;

/**
 * This is the model class for table "{{%user_feedback}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $username
 * @property integer $source
 * @property string $content
 * @property integer $telphone
 * @property integer $created_at
 */
class UserFeedback extends \yii\db\ActiveRecord
{
    public $feedback;
    public $picture;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_feedback}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'username', 'content', 'telphone', 'created_at','feedback'], 'required'],
            [['user_id', 'source', 'telphone', 'created_at','is_feedback'], 'integer'],
            [['content', 'username','feedback','number','model','system','attachment','picture'], 'string'],
            [['content'], 'string', 'max' => 500],
            [['attachment','feedback'], 'string', 'max' => 255],
            [['feedback'], 'trim'],
        ];
    }
    
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    public static function getNumber()
    {
        $number = CommonFun::getKeyValArr(new UserFeedback(), 'number', 'number');
        foreach($number as $k=>$v){
            if(empty($v)){
                unset($number[$k]);
            }
        }
        return $number;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户id',
            'username' => '用户名',
            'source' => '来源',
            'number' => '版本号',
            'content' => '内容',
            'telphone' => '联系方式',
            'feedback' => '回复内容',
            'picture' => '图片',
            'created_at' => '创建时间',
        ];
    }
}
