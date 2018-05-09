<?php
namespace common\models;

use Yii;
use common\models\Comment;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use common\functions\Tools;

class User extends ActiveRecord implements IdentityInterface
{
    public $userType;
    public $product_comment_num;
    public $acticle_comment_num;
    public $feedback_num;    
    public $askreply_num;
    public $user_product_num;
    public $user_inventory_num;
    public $skin_id;
    public $birth_date;
    public $communicate;

    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                //'value' => new Expression('NOW()'),
                //'value'=>$this->timeTemp(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username'], 'required'],
            [['sex', 'img_state','marital_status','birth_date','version'], 'integer'],
            [['referer','communicate', 'province', 'city'], 'string'],
            [['username', 'remark', 'img'], 'string', 'max' => 255],
            [['username','birth_date'], 'trim'],
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' =>1]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByMobile($mobile)
    {
        return static::findOne(['mobile' => $mobile]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function getAdmin()
    {
//         return $this->hasOne(Admin::className(), ['connect_user_id' => 'id']);
        return $this->hasOne(Admin::className(), ['id' => 'admin_id']);
    }

    public function getComment()
    {
        return $this->hasMany(Comment::className(), ['user_id' => 'id']);
    }
    
    public static function getAskReplyNum($id)
    {
        $ask = AskReply::find()->where("user_id = $id")->count();
        $reply = Ask::find()->where("user_id = $id")->count();
        $num = $ask + $reply;
    
        return $num;
    }
    
    public static function getFeedbackNum($id)
    {
        $num = UserFeedback::find()->where("user_id = $id")->count();
        return $num;
    }
    
    public static function getUserProductNum($id)
    {
        $num = UserProduct::find()->where("user_id = $id")->count();
        return $num;
    }
    
    public static function getUserInventoryNum($id)
    {
        $num = UserInventory::find()->where("user_id = $id")->count();
        return $num;
    }

    /**
         * 生成图片缩略图
         *
         * @access   public
         * @param    int      $width      图片宽
         * @param    int      $height     图片高
         * @param    string   $fugai      新文件名
         * @param    string   $path       图片路径
         * @param    point    $height     水印
         * @return   void
    */
    public static function thumbs($width=null,$height=null,$fugai=false,$path=null,$point=null){

        if(empty($width))$width = 50;
        if(empty($height))$height = 50;
        $width = intval($width);
        $height = intval($height);
        
        if(!file_exists($path)) {
          return false;
        }
        $imgSize = GetImageSize($path);
        $houzhui = explode('.',$path);
        $houzhui = array_pop($houzhui);
        $imgType = $imgSize[2];
        if(!is_array($point)){
          $point = ["x" => 0,"y" => 0,"w" => $imgSize[0],"h" => $imgSize[1]];
        }
        if ($point['w'] > $point['h']) {
          $point['x'] = intval(($point['w'] - $point['h'])/2);
          $point['w'] = $point['w'] - intval(($point['w'] - $point['h']));
        } else {
          $point['y'] = intval(($point['h'] - $point['w'])/2);
          $point['h'] = $point['h'] - intval(($point['h'] - $point['w']));
        }
        switch ($imgType)
        {
            case 1:
                $srcImg = @ImageCreateFromGIF($path);
                break;
            case 2:
                $srcImg = @ImageCreateFromJpeg($path);
                break;
            case 3:
                $srcImg = @ImageCreateFromPNG($path);
                break;
            default:
            $srcImg = @ImageCreateFromJpeg($path);
        }

        //缩略图片资源
        $targetImg = ImageCreateTrueColor($width,$height);
        $white = ImageColorAllocate($targetImg, 255,255,255);
        imagefill($targetImg,0,0,$white); // 从左上角开始填充背景
        ImageCopyResampled($targetImg,$srcImg,0,0,$point['x'],$point['y'],$width,$height,$point['w'],$point['h']);//缩放

        if($fugai){
            $tag_name = $fugai;
        }else{
            $tag_name = $path.'_'.$width.$height.'.'.$houzhui;  
        }

        switch ($imgType) {
                case 1:
                    //gif没有质量参数，使用jpeg方法缩略
                    ImageJpeg($targetImg,$tag_name,100);
                    // ImageGIF($targetImg,$tag_name);
                    break;
                case 2:
                    ImageJpeg($targetImg,$tag_name,100);
                    break;
                case 3:
                    ImagePNG($targetImg,$tag_name,9);
                    break;
                default:
                    ImageJpeg($targetImg,$tag_name,100);
                break;
            }
            ImageDestroy($srcImg);
            ImageDestroy($targetImg);
        return $houzhui;
      }

    /**
     * 更新用户名操作
     * @param int $uid 用户ID
     * @param string $username 用户名
     */
    public static function updateUsername($userId,$username) {

        if(empty($userId) || empty($username)){
            return false;
        }

        $comment_sql1    = "UPDATE {{%comment}} SET author = '$username' WHERE user_id = '$userId'";
        $feedback    = "UPDATE {{%user_feedback}} SET username = '$username' WHERE user_id = '$userId'";
        $ask_reply    = "UPDATE {{%ask_reply}} SET username = '$username' WHERE user_id = '$userId'";
        
        Yii::$app->db->createCommand($comment_sql1)->execute();
        Yii::$app->db->createCommand($feedback)->execute();
        Yii::$app->db->createCommand($ask_reply)->execute();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '用户ID',
            'username' => '用户名',
            'mobile' => '手机号',
            'grade' => '等级',
            'vip' => '积分',
            'img' => '头像',
            'img_state' => '头像状态',
            'sex' => '性别',
            'province' => '所在省',
            'city' => '所在城市',
            'marital_status' => '婚姻状况',
            'money' => '她币数',
            'group_num' => '圈子数',
            'interest' => '兴趣标签',
            'get_like_num' => '获赞数',
            'postDigestNum' => '加精数',
            'postStick' => '置顶数',
            'get_comment_num' => '评论数',//获评数
            'background_image' => '个人中心装扮图',
            'post_num' => '发帖数',
            'comment_num' => '评论数',
            'status' => '账号状态',
            'referer' => '来源',
            'remark' => '备注',
            'created_at' => '注册时间',
            'updated_at' => '最后修改时间',
            'product_comment_num' => '产品点评数',
            'acticle_comment_num' => '文章点评数',
            'user_product_num' => '我在用的',
            'user_inventory_num' => '我的清单',
            'feedback_num' => '反馈数',
            'askreply_num' => '问答数',
            'userType' => '类型',
            'admin_id' => '超级账号',
            'skin_id' => '肤质',
            'birth_date' => '生日',
            'version' => '版本',
        ];
    }
}
