<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%product_category}}".
 *
 * @property integer $id
 * @property string $cate_name
 * @property string $cate_img
 * @property integer $status
 * @property integer $created_at
 */
class ProductCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_category}}';
    }
    
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['child'] = ['cate_h5_img','cate_app_img','cate_name','sort'];
        return $scenarios;
    }
    
    public $product_num;
    public $question;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cate_name','status','sort'], 'required'],
            [['cate_h5_img','cate_app_img','cate_name','sort'],'required','on' => 'child'],
            [['status', 'created_at','sort','parent_id'], 'integer'],
            [['cate_name','budget'], 'string', 'max' => 100],
            [['cate_h5_img','cate_app_img'], 'string', 'max' => 255],
            [['cate_name','sort'], 'trim'],
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
    
    public function getProductDetails()
    {
        return $this->hasMany(ProductDetails::className(), ['cate_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '分类ID',
            'cate_name' => '分类名',
            'cate_h5_img' => 'h5分类图',
            'cate_app_img' => 'app分类图',
            'product_num' => '产品数',
            'status' => '状态',
            'budget' => '肤质推荐预算',
            'created_at' => '创建时间',
            'quesion' => '推荐问题',
            'parent_id' => '所属类别',
            'sort' => '排序',
        ];
    }
}
