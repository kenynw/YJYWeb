<?php
namespace common\models;

use Yii;
use yii\base\Model;
use common\models\User;
use common\functions\Tools;

/**
 * Signup form
 */
class ShadowForm extends Model
{
    public $username;
    public $adminId;
    public $mobile;
    public $_user;
    public $img;
    public $encoding;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],            
//             ['username', 'string', 'max' => 11],
            ['username','username'],
            ['adminId', 'required'],
            ['img', 'required'],
            ['username', 'safe'],
            ['adminId', 'integer'],
            [['mobile','img','username'], 'string'],
            ['username', 'unique2'],
        ];
    }

    public function username($attribute, $params)
    {
        if ($this->encoding === null) {
            $this->encoding = Yii::$app->charset;
        }
        $length = mb_strlen($this->username,$this->encoding);
        if ($length > 11) {
            $this->addError($attribute, "用户名最多11个字符");
        }
    }
    
    public function unique2($attribute, $params)
    {
        $username = addslashes(Tools::userTextEncode($this->username));
        $key = User::find()->where("username = '$username'")->one();
        if ($key) {
            $this->addError($attribute, "马甲名重复");
        }
    }
    
    public function createShadow()
    {
        if (!$this->validate()) {
            return null;
        }
    
        $user = new User();
        $user->username = Tools::userTextEncode($this->username);
        $user->admin_id = $this->adminId;
        $user->mobile = 'shadow';
        $user->status = '10';
        $user->referer = '';
        !empty($this->img)?$user->img = $this->img:$user->img = 'photo/member.png';
        $user->setPassword('shadow');
        $user->generateAuthKey();
        $user->save();
        $user->mobile = 'shadow' . $user->id;
        //修改头像
        User::get_thumb_avatar($user->id,$user->img);
        return $user->save() ? $user : null;
    }

    public function attributeLabels()
    {
        return [
            'id'       => '用户ID',
            'username' => '用户名',
            'mobile'   => '手机号',
            'img'      => '头像',
            'sex'      => '性别',
            'money'    => '她币',
            'status'   => '账号状态',
            'remark'   => '备注',
            'created_at' => '注册时间',
            'updated_at' => '最后修改时间',
        ];
    }
}
