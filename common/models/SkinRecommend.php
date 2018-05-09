<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%skin_recommend}}".
 *
 * @property integer $id
 * @property integer $category_id
 * @property integer $skin_id
 * @property string $skin_name
 * @property string $copy
 * @property string $reskin
 */
class SkinRecommend extends \yii\db\ActiveRecord
{
    public $reskin1;
    public $reskin2;
    public $reskin3;
    public $reskin4;
    public $reskin5;
    public $reskin6;
    public $reskin7;
    public $reskin8;
    public $noreskin1;
    public $noreskin2;
    public $noreskin3;
    public $noreskin4;
    public $noreskin5;
    public $noreskin6;
    public $noreskin7;
    public $noreskin8;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%skin_recommend}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'skin_id'], 'integer'],
//             [['skin_name'], 'required'],
            [['skin_name'], 'string', 'max' => 8],
            [['copy','reskin'],'string', 'max' => 255],
            [['copy','reskin','reskin1','reskin2','reskin3','reskin4','reskin5','reskin6','reskin7','reskin8','noreskin1','noreskin2','noreskin3','noreskin4','noreskin5','noreskin6','noreskin7','noreskin8'],'safe'],
            [['copy'], 'trim'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Category ID',
            'skin_id' => 'Skin ID',
            'skin_name' => 'Skin Name',
            'copy' => 'Copy',
            'reskin' => 'Reskin',
        ];
    }
}
