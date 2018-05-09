<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%brand}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $ename
 * @property string $alias
 * @property string $img
 * @property string $description
 * @property integer $cate_id
 * @property integer $is_recommend
 * @property string $hot
 * @property integer $created_at
 */
class Brand extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public $product_num;
    public $is_link;
    
    public static function tableName()
    {
        return '{{%brand}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['img', 'cate_id','description','hot'], 'required'],
            [['cate_id', 'is_recommend', 'hot','product_num','created_at','status','retime','parent_id','rule','is_auto'], 'integer'],            
            [['name', 'ename', 'alias', 'img'], 'string', 'max' => 150],
            [['description'], 'string', 'max' => 250],
            [['letter','link_tb','link_jd','is_link'], 'string'],
            [['link_tb','link_jd'],'url', 'defaultScheme' => 'http','message'=>'地址不是有效格式'],
            ['name','required','when' => function ($model) { return empty($model->ename);},'whenClient' => "function (attribute, value) {return $('#ename').value == '';}",'message' => '品牌中文名或英文名必填一项'],
            ['ename','required','when' => function ($model) { return empty($model->name);},'whenClient' => "function (attribute, value) {return $('#name').value == '';}",'message' => '品牌中文名或英文名必填一项'],
            [['name','ename','alias','description'], 'trim'],
            ['parent_id','default', 'value'=>'0'],
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
    
    public function getBrandCategory()
    {
        return $this->hasOne(BrandCategory::className(), ['id' => 'cate_id']);
    }
    
    public function getProductDetails()
    {
        return $this->hasMany(ProductDetails::className(), ['brand_id' => 'id']);
    }
    
    public function getBrand()
    {
        return $this->hasOne(Brand::className(), ['id' => 'parent_id']);
    }
    
    //品牌产品数
    static function getProduct($id,$type='0')
    {
        //总、上榜
        return empty($type) ? ProductDetails::find()->where("brand_id = $id")->count() : ProductDetails::find()->where("brand_id = $id AND is_top = 1")->count();
    }
    
    //品牌搜索列表
    static function getBrandList($id)
    {
        $data = Brand::find()
            ->select("id, CASE WHEN `name` IS null OR name='' THEN ename ELSE `name` END AS text")
            ->where("id = $id")
            ->asArray()
            ->all();

        $list = [];
        foreach ($data as $key=>$val) {
            $list[$val['id']] = $val['text'];
        }
        return $list;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '品牌ID',
            'name' => '品牌中文名',
            'ename' => '品牌英文名',
            'letter' => '首字母',
            'alias' => '别名',
            'img' => '品牌图',
            'description' => '品牌描述',
            'cate_id' => '品牌分类',
            'is_recommend' => '是否推荐',
            'status' => '状态',
            'hot' => '品牌热度',
            'product_num' => '总产品数',
            'link_tb' => '淘宝',
            'link_jd' => '京东',
            'created_at' => '创建时间',
            'is_link' => '有无返利',
            'parent_id' => '一级品牌',
            'is_auto' => '是否抓取'
        ];
    }
}
