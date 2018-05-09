<?php
namespace api\controllers;

use Yii;
use yii\web\Controller;
use common\functions\Functions;
/**
 * APP接口类
 */
class ApiController extends Controller
{  
    // 参数
    static $parameter     =   [];
    //公共必传参数
    static $required      =   ['time','from','action'];
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
        //时间
        $time       = time();
        $data       = [];
        $expireTime = $time - 600;
        //签名
        $data       = self::$parameter =  array_merge($_GET,$_POST);

        //无数据 
        if (empty($data))
        {
            $return = array('status' => '-6','msg'=>self::$ERROR['-6']);
            echo json_encode($return);die;
        }
        //过期
        if ($data['time'] < $expireTime)
        {
            $return = array('status' => '-9','msg'=>self::$ERROR['-9']);
            echo json_encode($return);die;
        }

        //公共参数验证
        $this->completeParameter();
        $version    = isset($data['version']) ? intval($data['version']) : '1';
        $api        = '\api\models\V'.$version;
        $apiObj     = new $api($data);

        //进入方法
        $action     = Functions::checkStr($data['action']);
        $callBack   = isset($data['callback']) ? Functions::checkStr($data['callback']) : '';

        if(method_exists($apiObj,$action)){
            $return = $apiObj->$action($data);
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
     *  作用：文档说明
     */
    public function actionMixed() {
        $version = Yii::$app->request->get('version');
        $version = $version ? $version : '';
        return $this->renderPartial('mixed'.$version);
    }
}
