<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%app_version}}".
 *
 * @property string $id
 * @property integer $type
 * @property string $content
 * @property string $number
 * @property integer $status
 * @property string $data_url
 * @property integer $create_time
 * @property integer $update_time
 */
class AppVersion extends \yii\db\ActiveRecord
{
    public $downloadUrl1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%app_version}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'number', 'status'], 'required'],
            [['type', 'status', 'create_time', 'update_time','isMust'], 'integer'],
            [['content'], 'string'],
            [['number'], 'string', 'max' => 50],
            [['downloadUrl'], 'string', 'max' => 255],
            ['downloadUrl','required','when' => function ($model) { return $model->type == '1';},'whenClient' => "function (attribute, value) {return $('#type').value == '1';}",'message' => '安卓类型请先上传文件'],
            [['number'], 'trim'],
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if($insert) {
                $this->creater_id =Yii::$app->user->identity->id;
                $this->create_time = $this->update_time = time();
            } else {
                $this->creater_id =Yii::$app->user->identity->id;
                $this->update_time = time();
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
            'type' => '类型',
            'content' => '更新内容',
            'number' => '版本号',
            'creater_id' => 'creater_id',
            'isMust' => '是否推送提示',
            'status' => '是否上线',
            'downloadUrl' => '下载地址',
            'downloadUrl1' => 'ios下载地址',
            'create_time' => '创建时间',
            'update_time' => 'Update Time',
        ];
    }
}
