<?php
namespace api\controllers;

use Yii;
use yii\web\Controller;
use common\functions\Functions;
use common\components\WeixinService;
/**
 * 
 * 微信服务接口
 */
class WeixinServiceController extends Controller
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
    // 微信回复入口
    public function actionAnswer(){
        $echostr = isset($_GET['echostr']) ? $_GET['echostr']: '';

        $service = new WeixinService();
        if($echostr){
            $service->valid(); //如果发来了echostr则进行验证
        }else{
            echo $service->sendTempMsg();
        }
    }
}
