<?php
namespace common\components;

use Yii;
use common\functions\Functions;
use common\components\OssUpload;

class Youtube {
    //下载地址
    static PUBLIC  $ERROR     = [
        '0'     =>  '操作失败',
        '1'     =>  '执行成功',
        '-1'    =>  '地址格式有误',
        '-2'    =>  'ffmpeg操作失败',
        '-3'    =>  'youtube 操作失败: %s '
    ];

    PUBLIC $urlPath     = '';
    PUBLIC $savePath    = '';
    PUBLIC $insertPath  = '';

    public function __construct(){
        set_time_limit(0);
        $this->savePath     = Yii::getAlias("@frontend").'/web/videos/temp/';
        $this->insertPath   = 'videos/temp/';
    }
    /**
     *下载视频
     *@param $file_path 需要上传的文件位置
     *@param $object    需要到的文件位置
    */
    public function uploadFile($urlPath){
        //初始化
        $regex      = "/^(http|https):\/\//";
        $return     = preg_match($regex, $urlPath);
        if(!$return){
          return ['status' => '-1','msg' => self::$ERROR['-1']];
        } 
        $this->urlPath = $urlPath;

        $exec = "youtube-dl ".$this->urlPath . ' --dump-json';
        exec($exec,$vivoInfo);
        if($vivoInfo && $vivoInfo[0]){
            $info       = json_decode($vivoInfo['0'],true);
        }else{
            return ['status' => '-1','msg' => $exec];
        }

        //验证是否存在
        $extName = $info['id'].'.'.$info['ext'];
        $filename= $this->savePath.$extName;
        //开始下载
        $uploadExec = "youtube-dl ".$this->urlPath.' -o '.$this->savePath.'%\(id\)s.%\(ext\)s';
        exec($uploadExec,$uploadInfo);
        if($uploadInfo){
            foreach ($uploadInfo as $key => $value) {
                if(preg_match('/100%/',$value)){
                    //下载完存入OSS
                    $return = $this->autoVideo($info);
                    if($return){
                        $isExist = $this->checkExist($return);
                        if(!$isExist) $this->insertVideo($info,$return);
                        //$this->uploadOssFile($extName,$return);
                        return ['status' => '1','msg' => $return];  
                    }else{
                        return ['status' => '0','msg' => self::$ERROR[-2]];
                    }
                }
            }
        }

        return ['status' => '-3','msg'=>sprintf(self::$ERROR['-3'], $uploadExec)];
    }
    /**
     *判断数据是否存在
     *@param $info 文件路径
     */
    public function checkExist($filename){
        $filename   = Functions::checkStr($filename);  //文件名
        //$filename2  = $this->insertPath.$filename;
        if(!$filename) return false;
        $sql        = "SELECT id FROM {{%video}} WHERE  video = '$filename'";
        $id         = Yii::$app->db->createCommand($sql)->queryScalar();
        return $id ? true : false;
    }
    /**
    *添加数据
    *@param $object 
    */
    public  function insertVideo($info,$return){
        $title      = Functions::checkStr($info['title']);      //标题
        // $filename   = Functions::checkStr($info['id']);         //文件名
        $filesize   = isset($info['filesize']) ? floatval($info['filesize']) : 0 ;              //大小：字节
        $thumbnail  = Functions::checkStr($info['thumbnail']);  //大小：字节
        $ext        = Functions::checkStr($info['ext']);        //文件类型 
        $url        = $this->urlPath;                           //地址
        $duration   = floatval(($info['duration']));            //时长：秒
        $description= isset($info['description']) ? Functions::checkStr($info['description']) :'';    //描述【非必有】

        $thumbImg   =   '';
        //保存缩略图
        if($thumbnail){
            $thumbImg = Functions::uploadUrlimg($thumbnail,'videos');
        }
        // $filename   = $filename.'.'.$ext;
        $insertSql  = "INSERT INTO {{%video}} (type,title,video,thumb_img,`desc`,link_url,filesize,ext,is_complete,duration) 
                        VALUES ('2','$title','$return','$thumbImg','$description','$url','$filesize','$ext','1','$duration')";
        $return     = Yii::$app->db->createCommand($insertSql)->execute();
        return $return;
    }
    /**
     *下载文件存入OSS
     *将object下载到指定的文件
     **@param $object       文件路径
     **@param $localfile    下载到位置
    */
    public  function uploadOssFile($file,$extName){                                     
        // $filename       =   $this->insertPath.$file;
        // $retuFile       =   'videos/'.$file;
        try {
            // $oss_obj = new OssUpload();
            // $is_upload = $oss_obj->upload($filename,$filename);
            // if(!$is_upload){
            //     return false;
            // }
            // $return = $retuFile;
            $this->updateVideo($file,$extName);
        } catch (Exception $e) {
            $return = $e->getMessage();
        }
        return $extName;
    }
    /**
     *修改为已完成状态
     **@param $retuFile      文件名
    */
    public  function updateVideo($file,$filename){
        if(!$file) return false;
        $sql        = "UPDATE {{%video}} SET is_complete = '1',video = '$filename' WHERE  video = '$file'";
        Yii::$app->db->createCommand($sql)->execute();
        return true;
    }
    /**
     *自动加片头片尾
     **@param $filename      文件名
    */
    public  function autoVideo($info){
        if(empty($info)) return false;
        $extName = $info['id'].'.'.$info['ext'];
        $filename= $this->savePath.$extName;
        $savePath     = $this->savePath;
        $returnFile   = time().rand(1,1000);
        $newFile      = $savePath.$returnFile;

        $commandFirst = 'ffmpeg -i '.$filename.' -c copy -bsf:v h264_mp4toannexb -f mpegts '.$newFile.'.ts';
        exec($commandFirst,$firstReturn);
        //目录
        $frontend = Yii::getAlias("@frontend").'/web/';
        $str = '';
        if(is_file($frontend.'videos/start.ts')){
            $str .= $frontend.'videos/start.ts|';
        }
        $str .= $newFile.'.ts';
        if(is_file($frontend.'videos/end.ts')){
            $str .= '|'.$frontend.'videos/end.ts';
        }
        $commandSecond='ffmpeg -i "concat:'.$str.'" -c copy -bsf:a aac_adtstoasc '.$newFile.'.'.$info['ext'];
        exec($commandSecond,$secondReturn);

        @unlink($newFile.'.ts'); 
        if(is_file($newFile.'.'.$info['ext'])){
            return $this->insertPath.$returnFile.'.'.$info['ext'];
        }
        return false;
        
    }
}
