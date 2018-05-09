<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use common\models\AskReply;
/**
 * This is the model class for table "{{%product_details}}".
 *
 * @property integer $id
 * @property string $product_name
 * @property string $brand
 * @property integer $status
 * @property integer $is_recommend
 * @property integer $cate_id
 * @property string $price
 * @property string $form
 * @property integer $star
 * @property string $standard_number
 * @property string $product_country
 * @property integer $product_date
 * @property string $product_company
 * @property string $en_product_company
 * @property string $component_id
 * @property integer $created_at
 */
class ProductDetails extends \yii\db\ActiveRecord
{
    public $component_id;
    public $tag_name;
    public $new_tag;
    public $link1;
    public $link2;
    public $link3;
    public $tb_goods_id1;
    public $tb_goods_id2;
    public $tb_goods_id3;
    public $is_link;
    public $askreply_num;
    public $tag_name2;
    public $new_tag2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_details}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//             [['product_name', 'brand', 'cate_id', 'price', 'form', 'star', 'standard_number', 'product_country', 'product_date', 'product_company', 'en_product_company', 'component_id', 'created_at'], 'required'],
            [['product_name','cate_id'], 'required'],
            [['status', 'is_recommend', 'cate_id', 'star', 'created_at','has_img','brand_id','recommend_time','tb_goods_id1','tb_goods_id2','tb_goods_id3','ranking','edit_img','askreply_num','is_complete'], 'integer'],
            ['is_top', 'integer'],
            ['price','default', 'value'=>'0'],
            ['brand_id','default', 'value'=>'0'],
            [['product_company','en_product_company','product_explain','product_img'], 'string', 'max' => 255],
            [['standard_number'], 'string', 'max' => 150],
            [['form'], 'string', 'max' => 10],
            [['product_name','remark','alias'], 'string', 'max' => 1000],
            [['product_country'], 'string', 'max' => 20],
            [['product_date'],'safe'],
            [['tag_name','new_tag'],'checkTagName'],
            [['tag_name2','new_tag2'],'checkTagName2'],
            [['link1','link2','link3'],'url', 'defaultScheme' => 'http','message'=>'地址不是有效格式'],
            [['link1','link2','link3','tb_goods_id1','tb_goods_id2','tb_goods_id3'],'checkLink'],
            [['product_name','alias','price','form','standard_number','product_country','product_company','en_product_company','link1','link2','link3','tb_goods_id1','tb_goods_id2','tb_goods_id3','product_explain'], 'trim'],
        ];
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                //'value' => new Expression('NOW()'),
                //'value'=>$this->timeTemp(),
            ],
        ];
    }
    
    public function checkTagName($attribute, $params)
    {
        $new_tag = $this->new_tag === '' ? '0' : count(explode(',', substr($this->new_tag,0,strlen($this->new_tag)-1)));
        $tag_name = $this->tag_name === '' ? '0' : count($this->tag_name);
        if ($tag_name + $new_tag > 3) {
            $this->addError('tag_name', "产品功效标签最多三个");
            $this->addError('new_tag', "产品功效标签最多三个");
        };
    }
    
    public function checkTagName2($attribute, $params)
    {
        $new_tag = $this->new_tag2 === '' ? '0' : count(explode(',', substr($this->new_tag2,0,strlen($this->new_tag2)-1)));
        $tag_name = $this->tag_name2 === '' ? '0' : count($this->tag_name2);
        if ($tag_name + $new_tag > 2) {
            $this->addError('tag_name2', "产品标签最多两个");
            $this->addError('new_tag2', "产品标签最多两个");
        };
    }
    
    public function checkLink($attribute, $params)
    {
        for($i=1;$i<=3;$i++){
            $link = 'link'.$i;
            $tb_goods_id = 'tb_goods_id'.$i;
            if ((empty($this->$link) && !empty($this->$tb_goods_id)) || (!empty($this->$link) && empty($this->$tb_goods_id))) {
                $this->addError($tb_goods_id, "购买渠道和商品id必须都填");
                $this->addError($link, "购买渠道和商品id必须都填");
            }
        }
    }    
    
    public function getProductCategory()
    {
        return $this->hasOne(ProductCategory::className(), ['id' => 'cate_id']);
    }
    
    public function getProductComponent()
    {
        return $this->hasOne(ProductComponent::className(), ['id' => 'component_id']);
    }
    
    public function getBrand()
    {
        return $this->hasOne(Brand::className(), ['id' => 'brand_id']);
    }
    
    public function getProductRelate()
    {
        return $this->hasMany(ProductRelate::className(), ['product_id' => 'id']);
    }
    
    public static function getAskReplyNum($id)
    {
        $ask = AskReply::find()->joinWith('ask')->where("product_id = $id")->count();
        $reply = Ask::find()->where("product_id = $id")->count();
        $num = $ask + $reply;
        
        return $num;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '产品ID',
            'product_img' => '产品图',
            'product_name' => '产品名',
            'brand_id' => '品牌',
            'status' => '状态',
            'is_recommend' => '是否推荐',
            'is_top' => '是否上榜',
            'cate_id' => '分类',
            'price' => '参考价',
            'form' => '规格',
            'star' => '星级',
            'standard_number' => '备案号',
            'product_country' => '生产国',
            'product_date' => '批准日期',
            'product_company' => '生产企业（中）',
            'en_product_company' => '生产企业（英）',
            'product_explain' => '颜究院解读',
            'component_id' => '成分',
            'tag_name' => '产品功效',
            'tag_name2' => '产品标签',
            'created_at' => '创建时间',
            'has_img' => '是否有图',
            'is_link' => '有无返利',
            'comment_num' => '评论数',
            'tb_goods_id1' => '关联平台的商品id',
            'tb_goods_id2' => '关联平台的商品id',
            'tb_goods_id3' => '关联平台的商品id',
            'remark' => '别名',
            'askreply_num' => '问答数'
        ];
    }
}
