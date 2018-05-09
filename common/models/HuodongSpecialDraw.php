<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%huodong_special_draw}}".
 *
 * @property string $id
 * @property integer $hdid
 * @property integer $uid
 * @property string $username
 * @property double $prize
 * @property integer $giftid
 * @property string $giftname
 * @property string $addtime
 * @property string $ip
 * @property string $ext
 * @property integer $sendstatus
 */
class HuodongSpecialDraw extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%huodong_special_draw}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['hdid', 'uid', 'giftid'], 'required'],
            [['hdid', 'uid', 'giftid', 'addtime', 'sendstatus','endtime'], 'integer'],
            [['prize'], 'number'],
            [['username', 'ip'], 'string', 'max' => 15],
            [['giftname'], 'string', 'max' => 255],
            [['ext'], 'string', 'max' => 1000],
        ];
    }
    
    public function getHuodongAddress()
    {
        return $this->hasOne(HuodongAddress::className(), ['user_id' => 'uid','hid' => 'hdid']);
    }
    
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }
    
    public function getHuodongSpecialConfig()
    {
        return $this->hasOne(HuodongSpecialConfig::className(), ['id' => 'hdid']);
    }
    
    public static function getHuodongNameArr() {
        $huodongName = HuodongSpecialConfig::find()->select('id,name')->all();
        $huodongNameArr = [];
        if ($huodongName){
            foreach ($huodongName as $key => $val) {
                $huodongNameArr[$val->id] = $val->name;
            }
        }
        return $huodongNameArr;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'hdid' => '活动ID',
            'uid' => 'Uid',
            'username' => '用户名',
            'prize' => '奖品金额',
            'giftid' => '是否中奖',
            'giftname' => '奖品名称',
            'addtime' => '活动发起时间',
            'ip' => 'Ip',
            'ext' => '其他',
            'sendstatus' => '状态',
        ];
    }
}
