<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%app_menu}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $subtitle
 * @property string $img
 * @property integer $sort
 * @property integer $status
 * @property integer $type
 * @property string $relation
 * @property string $add_time
 */
class AppMenu extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%app_menu}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'subtitle', 'sort', 'status','type'], 'required'],
            [['sort', 'status', 'type'], 'integer'],
            [['add_time'], 'safe'],
            [['title', 'subtitle', 'img'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '频道名称',
            'subtitle' => '副标题',
            'img' => '图片',
            'sort' => '排序',
            'status' => '状态',
            'type' => '类型',
            'add_time' => '创建时间',
        ];
    }
}
