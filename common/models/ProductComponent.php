<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%product_component}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $risk_grade
 * @property integer $is_active
 * @property integer $is_pox
 * @property string $component_action
 * @property string $description
 * @property integer $created_at
 */
class ProductComponent extends \yii\db\ActiveRecord
{
    public $product_num;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_component}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'/* , 'risk_grade' */], 'required'],
            [[ 'is_active', 'is_pox', 'created_at'], 'integer'],
            [['name'], 'unique'],
            [['name'], 'string', 'max' => 320],
            [['ename','alias'], 'string', 'max' => 500],
            [['risk_grade'], 'string', 'max' => 50],
            [['component_action'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 1000],
            [['name','ename','risk_grade','alias','description'], 'trim'],
            
            ['risk_grade','match','pattern'=>'/^[0-9\-]+$/','message'=>'只能输入数字范围'],
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if($insert) {
                $this->created_at = time();
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '  成分名',
            'risk_grade' => '安全风险',
            'is_active' => '活性成分',
            'is_pox' => '致痘',
            'component_action' => '使用目的',
            'description' => '简介',
            'created_at' => '创建时间',
            'ename' => '英文名',
            'alias' => '成分别名',
            'product_num' => '产品数',
            'cas' => 'CAS号',
        ];
    }
}
