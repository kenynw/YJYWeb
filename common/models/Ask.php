<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%ask}}".
 *
 * @property string $askid
 * @property string $subject
 * @property string $content
 * @property string $username
 * @property integer $user_id
 * @property integer $status
 * @property integer $product_id
 * @property string $product_name
 * @property integer $add_time
 */
class Ask extends \yii\db\ActiveRecord
{
    public $id;
    public $total;
    public $type;
    public $reply;
    public $a_name;
    public $a_uid;
    public $r_name;
    public $r_uid;
    public $userType;
    public $replyid;
    public $picture;

    public static function tableName()
    {
        return '{{%ask}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content', 'username', 'user_id','ask'], 'required'],
            [['user_id', 'status', 'product_id', 'add_time','admin_id'], 'integer'],
            [['product_name'], 'string', 'max' => 100],            
            [['subject', 'content', 'username','picture'], 'string', 'max' => 255],
            [['subject', 'content'], 'trim'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'askid' => 'Askid',
            'subject' => '标题',
            'content' => '内容',
            'username' => '用户名',
            'user_id' => '用户ID',
            'status' => '状态，1为正常，0为不正常',
            'product_id' => '产品ID',
            'product_name' => '产品名',
            'add_time' => '问答时间',
            'type' => '来源'
        ];
    }
}
