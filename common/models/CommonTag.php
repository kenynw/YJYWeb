<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%common_tag}}".
 *
 * @property string $tagid
 * @property string $tagname
 * @property integer $status
 */
class CommonTag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%common_tag}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tagname'], 'required'],
            [['status','count','type'], 'integer'],
            [['tagname'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tagid' => 'Tagid',
            'tagname' => 'Tagname',
            'status' => 'Status',
        ];
    }
}
