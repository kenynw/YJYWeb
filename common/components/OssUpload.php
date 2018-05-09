<?php
namespace common\components;

use Yii;
use common\functions\Functions;

require_once 'OSS/OSSConfig.php';
require_once 'OSS/autoload.php';
require_once 'OSS/OssClient.php';
require_once 'OSS/OSS/Core/OssUtil.php';
require_once 'OSS/OSS/Core/OssException.php';

use OSS\OssClient;
use OSS\Core\OssUtil;
use OSS\Core\OssException;

//OSS 上传类 FROM LIAO XIAN FNEG
class OssUpload {
    const endpoint          =   OSS_ENDPOINT;     //OSS参数
    const accessKeyId       =   OSS_ACCESS_ID;    //OSS参数
    const accesKeySecret    =   OSS_ACCESS_KEY;   //OSS参数
    const bucket            =   OSS_TEST_BUCKET;  //OSS参数

    static private $bucket    = '';                //上传到路径
    static private $oss       = '';                //OSS 类
    static private $ossPath   = 'http://oss.yjyapp.com/';
    //设置上传配置参数
    public function __construct(){
        //初始化
        try {
            self::$oss = new OssClient(self::accessKeyId, self::accesKeySecret, self::endpoint);
            self::$bucket = self::bucket;
        } catch (OssException $e) {
            echo  $e->getMessage();
        }
    }
    /**
     *简单上传
     *上传指定的本地文件内容
     *@param $file_path 需要上传的文件位置
     *@param $object    需要到的文件位置
    */
    public function upload($file_path,$object){

        try {
            $res     = self::$oss->uploadFile(self::$bucket, $object, $file_path);
            Functions::uploadOssimg(self::$ossPath.$object);
        } catch (OssException $e) {
            return  $e->getMessage();
        }
        return true;
    }
    /**
     *判断object是否存在
     *@param $object 文件路径
     */
    public function isExist($object){
        try {
            $res = self::$oss->doesObjectExist(self::$bucket, $object);
        } catch (OssException $e) {
            return  $e->getMessage();
        }
        return $res;
    }
    /**
    *删除object
    *@param $object 文件路径
    */
    public  function delFile($object){
        if(!$this->isExist($object)) return false;
        try {
            $res = self::$oss->deleteObject(self::$bucket, $object);
        } catch (OssException $e) {
            return  $e->getMessage();
        }
        return $res;
    }
    /**
     *获取object
     *将object下载到指定的文件
     **@param $object       文件路径
     **@param $localfile    下载到位置
    */
    public  function getFile($object,$localfile = ''){

        if(!$this->isExist($object)) return false;
        $res    = self::$oss->getObject(self::$bucket, $object);
        file_put_contents($localfile,$res);
        return true;
    }
}
