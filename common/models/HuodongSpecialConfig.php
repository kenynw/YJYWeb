<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%huodong_special_config}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $prize
 * @property integer $prize_num
 * @property integer $status
 * @property integer $starttime
 * @property integer $endtime
 * @property string $performer
 * @property integer $addtime
 */
class HuodongSpecialConfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%huodong_special_config}}';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name' , 'starttime', 'endtime','prize','relation','prize_num','picture'], 'required'],
            [['prize_num', 'status','re_number','type'], 'integer'],
            [['re_number'], 'default', 'value' => 0],
            ['addtime','safe'],          
            [['name','prize'], 'string', 'max' => 50],
            [['notice','prize','relation','picture'], 'string', 'max' => 255],
            ['relation','url', 'defaultScheme' => 'http','message'=>'地址不是有效格式','when' => function($model){ return ($model->type == '0'); },'whenClient' => "function (attribute, value) { return $('#type').val() == '0'; }"],
            ['relation','integer','message'=>'ID必须是整数','when' => function($model){ return ($model->type == '1' ||$model->type == '2'); },'whenClient' => "function (attribute, value) { return $('#type').val() == '1' || $('#type').val() == '2'; }"],
            [['starttime', 'endtime'],'checkTime'],
            [['name','prize','prize_num','re_number','notice'], 'trim'],
        ];
    }
    
    public function checkTime($attribute, $params)
    {
        if (!empty($this->starttime) && !empty($this->endtime)) {
            if (is_int($this->starttime) && is_int($this->starttime)) {
                //后台验证
                $differ = $this->endtime - $this->starttime;
            } else {
                //js验证
                $differ = strtotime($this->endtime) - strtotime($this->starttime);
            }
            
            if ($differ < 0) {
                $this->addError($attribute, "结束时间不能早于开始时间");
            } elseif ($differ == 0) {
                $this->addError($attribute, "结束时间不能等于开始时间");
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '活动名称',
            'prize' => '奖品名称',
            'prize_num' => '奖品数',
            're_number' => '助攻数',
            'status' => '状态',
            'type' => '类型',
            'relation' => '关联ID或链接',
            'notice' => '通知内容',
            'picture' => 'banner',
            'starttime' => '开始时间',
            'endtime' => '结束时间',
            'performer' => '创建人',
            'addtime' => '添加时间',
        ];
    }
}
