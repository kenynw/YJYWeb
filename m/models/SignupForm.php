<?php
namespace frontend\models;

use yii\base\Model;
use common\models\User;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $password;
    public $mobile;
    public $referer;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['mobile', 'filter', 'filter' => 'trim'],
            ['mobile', 'required'],
            ['mobile', 'unique', 'targetClass' => '\common\models\User', 'message' => '该手机号已注册.'],
            ['mobile', 'match', 'pattern' => '/^1[34578]{1}\d{9}$/', 'message' => '手机号格式错误.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
            ['referer', 'string']
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        
        $user = new User();
        $user->username = substr($this->mobile, 0, 3) . '****' . substr($this->mobile, 7, 10);
        $user->mobile = $this->mobile;
        $user->status = '10';
        //$user->referer = '2';
        $user->referer = isset($this->referer) ? $this->referer : "";
        $user->img = 'photo/member.png';
        $user->setPassword($this->password);
        $user->generateAuthKey();
        return $user->save() ? $user : null;
    }
}
