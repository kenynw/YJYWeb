<?php

namespace common\models;

use Yii;
use backend\models\CommonFun;
use yii\base\Object;

/**
 * This is the model class for table "{{%video}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $video
 * @property string $thumb_img
 * @property string $desc
 * @property string $product_id
 * @property string $click_num
 * @property integer $like_num
 * @property integer $comment_num
 * @property string $link_url
 * @property string $filesize
 * @property string $ext
 * @property string $duration
 * @property integer $is_complete
 * @property integer $status
 * @property string $add_time
 */
class Video extends \yii\db\ActiveRecord
{
    public $file;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%video}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['desc','status','duration','title','thumb_img','video','icon_img'], 'required'],
            [['click_num', 'like_num', 'comment_num', 'filesize', 'is_complete', 'status','type'], 'integer'],
            [['add_time','update_time'], 'safe'],
            [['title',  'thumb_img', 'desc', 'link_url','icon_img'], 'string', 'max' => 255],
//             [['product_id'], 'string', 'max' => 150],
            [['ext','duration'], 'string', 'max' => 50],
            [['file'], 'file'], 
            [['product_id'], 'safe'],
            [['duration'],'checkTime'],
            [['desc','title'],'trim']
        ];
    }
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if($insert) {
                $this->update_time = time();
            }else{
                $this->update_time = time();
            }
            return true;
        } else {
            return false;
        }
    }
    
    public function checkTime($attribute, $params)
    {
        if ($this->duration == '00:00') {
            $this->addError($attribute, "时长不能为空");
        } elseif (!preg_match('/[0-5]{1}[0-9]{1}[:][0-5]{1}[0-9]{1}$/', $this->duration, $matches)) {
            $this->addError($attribute, "时长格式不对");
        }
    }
    
    static public function getProductStr($id)
    {
        $model = Video::findOne($id);
        if ($model) {
            $productStr = "";
            $product = explode(',', $model->product_id);
            
            $product = CommonFun::getConnectArr($product,new ProductDetails(),'id','product_name');
            $i = 1;
            foreach ($product as $key=>$val) {
                $productStr .= $i.".&nbsp;";
                $productStr .= $val;
                $productStr .= "<br>";
                $i += 1;
            }
        }    
        return $productStr;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '视频id',
            'title' => '视频标题',
            'video' => 'Video',
            'thumb_img' => '封面图',
            'icon_img' => 'icon图',
            'desc' => '说明',
            'product_id' => '相关产品（仅支持搜索已上架产品）',
            'click_num' => 'Click Num',
            'like_num' => 'Like Num',
            'comment_num' => 'Comment Num',
            'link_url' => 'Link Url',
            'filesize' => 'Filesize',
            'ext' => 'Ext',
            'duration' => '时长',
            'is_complete' => '抓取进度',
            'status' => '状态',
            'add_time' => '创建时间',
            'update_time' => '编辑时间',
            'file' => '视频'
        ];
    }
}
