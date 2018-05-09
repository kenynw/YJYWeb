<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%brand_category}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $status
 * @property integer $created_at
 */
class BrandCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%brand_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'status'], 'required'],
            [['status', 'created_at', 'sort'], 'integer'],
            ['sort','default','value' => 0],
            [['name'], 'string', 'max' => 150],
            [['name','sort'], 'trim'],
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
    
    //品牌数
    static function getBrand($id)
    {
        return Brand::find()->where("cate_id = $id")->count();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '分类ID',
            'name' => '分类名',
            'status' => '状态',
            'sort' => '排序',
            'created_at' => '创建时间',
        ];
    }
}
