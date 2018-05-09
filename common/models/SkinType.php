<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%skin_type}}".
 *
 * @property integer $skin_id
 * @property integer $min
 * @property integer $max
 * @property string $name
 * @property string $unscramble
 * @property integer $order
 * @property string $add_time
 */
class SkinType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%skin_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//             [['min'],'required'],
            [['min', 'max', 'order', 'add_time'], 'integer'],
            [['name_en'], 'string', 'max' => 10],
            [['unscramble'], 'string', 'max' => 255],
            [['name_en','min','max','unscramble'], 'trim'],
        ];
    }
    
    public static function getTestType($name_en) {
        $test = [
            'O' => '油性干性测试',
            'D' => '油性干性测试',
            'R' => '耐受敏感测试',
            'S' => '耐受敏感测试',
            'N' => '色素非色素测试',
            'P' => '色素非色素测试',
            'T' => '皱纹紧致测试',
            'W' => '皱纹紧致测试',
        ];
        
        if (array_key_exists($name_en, $test)) {
            $return = $test[$name_en];
        } else {
            $return = '';
        }
        return $return;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'skin_id' => 'Skin ID',
            'name' => '肤质类型',
            'name_en' => '英文',
            'min' => '最小值',
            'max' => '最大值',
            'unscramble' => '肤质解读',
            'order' => 'Order',
            'add_time' => 'Add Time',
        ];
    }
}
