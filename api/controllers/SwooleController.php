<?php
namespace api\controllers;

use Yii;
use yii\web\Controller;
use common\functions\Functions;
use common\components\OssUpload;

/**
 * APP接口类
 */
class SwooleController extends Controller
{  
    // 参数
    static $parameter     =   [];
    //解释后参数
    static $data          =   [];
    //公共必传参数
    static $required      =   ['action'];
    //支持POST
    public $enableCsrfValidation = false;
    //错误码
    static $ERROR         =   array(
        '0'     =>  '处理失败',
        '1'     =>  '处理成功',
        '-1'    =>  '帐号或密码错误',
        '-2'    =>  '账户不存在',
        '-3'    =>  '参数不完整,缺少 %s 参数',
        '-4'    =>  '注册失败',
        '-5'    =>  '%s 方法未定义',
        '-6'    =>  'TOKEN错误',
        '-7'    =>  'ip受限',
        '-8'    =>  '产品不存在',
        '-9'    =>  '时间过期',
        '-10'   =>  '帐号已被封号',
        '-11'   =>  '帐号异常',
        '-12'   =>  '验证码无效或已过期',
        '-13'   =>  '短信发送失败',
        '-14'   =>  '手机格式有误',
        '-15'   =>  '评论不存在',
        '-16'   =>  '文章不存在',
        '-17'   =>  '用户已存在',
        '-18'   =>  '帐号已被禁言',
        '-200'  =>  '其他错误'
    );
    //TOKEN验证
    static $tokenAction   =   [
                // 'uploadImg',
                'userInfo',
            ];
    /**
     * [__construct 构造函数]
     * @param [type] $data [description]
     */
    PUBLIC function init() {
        //时间
        $data       = [];
        //签名
        $data       = self::$parameter =  array_merge($_GET,$_POST);
        //无数据 
        if (empty($data))
        {
            $return = array('status' => '-6','msg'=>self::$ERROR['-6']);
            echo json_encode($return);die;
        }

        //公共参数验证
        $this->completeParameter();
        self::$data = $data;
        //验证TOKEN
        self::$data['user_id'] = isset($data['token']) ? Functions::checkToken($data['token']) : 0 ;
   }
    /**
     *  作用：检查参数完整性
     */
    protected function completeParameter($required = array()) {
        //验证参数完整性
        $data       = self::$parameter;
        $required   = $required ? $required : self::$required;
        $return     = false;
        foreach ($required as $key => $value) {
            if(!isset($data[$value]) || empty($data[$value])){
                $return = array('status' => '-3','msg'=>sprintf(self::$ERROR['-3'], $value));
                break;
            }
        }
        //不完整不通过
        if($return){ echo  json_encode($return);die;}
    }
    // 通用验证参数
    public function actionIndex(){
        //进入方法
        $action     = Functions::checkStr(self::$data['action']);
        $callBack   = isset(self::$data['callback']) ? Functions::checkStr(self::$data['callback']) : '';

        if(method_exists($this,$action)){
            $return = $this->$action();
        }else{
            $return = array('status' => '-5','msg'=>sprintf(self::$ERROR['-5'], $data['action']) );
            $return = json_encode($return);
        }
        //兼容JSONP跨域
        if($callBack){
            echo  $callBack.'('.$return.')';
        }else{
            echo  $return;
        }
    }
    /**
    * 图片上传接口
    * @para string $sign      校验码
    * @para int    $time      时间
    * @para string $imgFile   baes64后的2进制文件
    * @return json 成功图片地址，失败返回错误
    */
    public function uploadImg()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Max-Age: 1000');

        //验证
        $requiredParameter  = array('imgFile');
        $this->completeParameter($requiredParameter);
        //参数
        $type       = 'swoole';
        $imgFile    = base64_decode(trim(self::$data['imgFile']));

        $imgType    = Functions::check_image_type($imgFile);
        $imgTypeArr = array('jpg','gif','png');

        if(!in_array($imgType,$imgTypeArr)){
            $data = ['status' => 0, 'msg' => '文件上传失败'];
            return  json_encode($data);
        }
        $userId     = intval(self::$data['user_id']);
        //准备上传
        $serverName =  Yii::$app->params['isOnline']; //$_SERVER['SERVER_NAME'];

        if($serverName){
            $savePath   =   'uploads';
        }else{
            $savePath   =   'cs/uploads';
        }
        $img_name   =   $type .'/'.$userId.'/'.date('Ymd').'/'.time().rand(100000,999999).".".$imgType;                                       
        $filename   =   $savePath.'/'.$img_name;

        $ossFile    =   $filename;
        $dirname    =   dirname($filename);

        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
            $data = ['status' => 0, 'msg' => '文件创建失败'];
            return  json_encode($data);
        }

        try {
            file_put_contents($filename,$imgFile);
            $oss_obj = new OssUpload();
            $is_upload = $oss_obj->upload($filename,$filename);
            if(!$is_upload){
                return false;
            }
            $data = ['status' => 1, 'msg' => $filename];
        } catch (Exception $e) {
            $data = ['status' => 0, 'msg' => $e->getMessage()];
        }
        return  json_encode($data);
    }
    /**
    * 用户资料接口
    * @para int     $user_id    用户ID
    * @return json 返回用户信息
    */
    PUBLIC function userInfo(){
        $userId     = intval(self::$data['user_id']);
        //查询
        $userInfo   = Functions::getUserInfo($userId);

        if($userInfo){
            $data = ['status' => 1, 'msg' => $userInfo];
        }else{
            $data = ['status' => 0, 'msg' => self::$ERROR['-2']];
        }
        return  json_encode($data);
    }
}
