<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%common_tagitem}}".
 *
 * @property string $tagid
 * @property string $itemid
 * @property string $idtype
 */
class CommonTagitem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%common_tagitem}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tagid', 'itemid'], 'required'],
            [['tagid', 'itemid','idtype'], 'integer'],
//             [['idtype'], 'string', 'max' => 10],
            [['tagid', 'itemid', 'idtype'], 'unique', 'targetAttribute' => ['tagid', 'itemid', 'idtype'], 'message' => 'The combination of Tagid, Itemid and Idtype has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tagid' => 'Tagid',
            'itemid' => 'Itemid',
            'idtype' => 'Idtype',
        ];
    }
}
