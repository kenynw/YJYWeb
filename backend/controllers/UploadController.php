<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use common\models\User;

class UploadController extends Controller
{

	public $filePath = '@frontend/web/uploads/';

	public function actionUploadImg()
	{
		$file = $_FILES['file_upload'];
		if (!$file) {
            return json_encode(['status' => '1', 'message' => '找不到上传文件']);
        }
        if ($file['error']) {
            return json_encode(['status' => '1', 'message' => $file['error']]);
        } else if (!file_exists($file['tmp_name'])) {
            return json_encode(['status' => '1', 'message' => '找不到临时文件']);
        } else if (!is_uploaded_file($file['tmp_name'])) {
            return json_encode(['status' => '1', 'message' => '临时文件错误']);
        }
        $ext = '.jpg';
        if (exif_imagetype($file['tmp_name']) == IMAGETYPE_GIF) {
        	$ext = '.gif';
        }
        if (exif_imagetype($file['tmp_name']) == IMAGETYPE_JPEG) {
        	$ext = '.jpeg';
        }
        if (exif_imagetype($file['tmp_name']) == IMAGETYPE_PNG) {
        	$ext = '.png';
        }
		$savePath = Yii::getAlias($this->filePath);
		$randNum = rand(100000, 10000000000);
		$savename = 'post/' . date('Ymd') . '/' . time() . substr($randNum, 0, 6) . $ext;
		$savePath = $savePath . $savename;
		$this->mkdirs($savePath);
		if(move_uploaded_file($file['tmp_name'], $savePath)){
			return json_encode(['data' => 'success', 'filename' => $savename]);
		}else{
		    return json_encode(['data' => 'error']);
		}
	}

	public function actionBaseUploadImg()
    {   
        $base64_url = Yii::$app->request->post('base64');
        $base64_body = substr(strstr($base64_url,','),1);
        $imgType = explode('/', explode(';', $base64_url)[0])[1];
        if (empty($imgType)) {
            $imgType = 'jpeg';
        }
        $data = base64_decode($base64_body);
        $savePath = Yii::getAlias($this->filePath);
        $randNum = rand(100000, 10000000000);
        $savename = 'post/' . date('Ymd') . '/' . time() . substr($randNum, 0, 6) . '.' . $imgType;
        $savePath = $savePath . $savename;
        $this->mkdirs($savePath);
        file_put_contents($savePath,$data);
        User::thumbs(180,180,false,$savePath);
        if(file_exists('../web/uploads/'.$savename.'_180180.jpeg')) {
            $thumbsPath = $savename .'_180180.jpeg';
        } else {
            $thumbsPath = $savename;
        }
        return json_encode(['status' => '0', 'message' => 'success', 'filename' => $savename, 'thumbs' => $thumbsPath]);
    }

	//生成图片的缩略图，30*30,50*50,100*100
	//是否覆盖 0不覆盖 ，1覆盖
	//$path 外部指定文件
// 	public function thumbs($width=null,$height=null,$fugai=false,$path=null,$point=null){

// 		if(empty($width))$width = 50;
// 		if(empty($height))$height = 50;
// 		$width = intval($width);
// 		$height = intval($height);
		
// 		if(!file_exists($path)) {
// 			return false;
// 		}
// 		$imgSize = GetImageSize($path);
// 		$houzhui = explode('.',$path);
// 		$houzhui = array_pop($houzhui);
//         $imgType = $imgSize[2];
// 		if(!is_array($point)){
// 			$point = ["x" => 0,"y" => 0,"w" => $imgSize[0],"h" => $imgSize[1]];
// 		}
// 		if ($point['w'] > $point['h']) {
// 			$point['x'] = intval(($point['w'] - $point['h'])/2);
// 			$point['w'] = $point['w'] - intval(($point['w'] - $point['h']));
// 		} else {
// 			$point['y'] = intval(($point['h'] - $point['w'])/2);
// 			$point['h'] = $point['h'] - intval(($point['h'] - $point['w']));
// 		}
//         switch ($imgType)
//         {
//             case 1:
//                 $srcImg = @ImageCreateFromGIF($path);
//                 break;
//             case 2:
//                 $srcImg = @ImageCreateFromJpeg($path);
//                 break;
//             case 3:
//                 $srcImg = @ImageCreateFromPNG($path);
//                 break;
// 			default:
// 				$srcImg = @ImageCreateFromJpeg($path);
//         }
		
// 		//缩略图片资源
// 		$targetImg = ImageCreateTrueColor($width,$height);
// 		$white = ImageColorAllocate($targetImg, 255,255,255);
// 		imagefill($targetImg,0,0,$white); // 从左上角开始填充背景
// 		ImageCopyResampled($targetImg,$srcImg,0,0,$point['x'],$point['y'],$width,$height,$point['w'],$point['h']);//缩放
// 		if($fugai){
// 			$tag_name = '';
// 		}else{
// 			$tag_name = '_'.$width.$height.'.'.$houzhui;
// 		}
		
// 		switch ($imgType) {
//             case 1:
//                 ImageGIF($targetImg,$path.$tag_name);
//                 break;
//             case 2:
//                 ImageJpeg($targetImg,$path.$tag_name);
//                 break;
//             case 3:
//                 ImagePNG($targetImg,$path.$tag_name);
//                 break;
// 			default:
// 				ImageJpeg($targetImg,$path.$tag_name);
//                 break;
// 			;
//         }
//         ImageDestroy($srcImg);
//         ImageDestroy($targetImg);
// 		return $houzhui;
// 	}

	private function mkdirs($filePath)
	{
		$dirname = dirname($filePath);
		if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
			return json_encode(['status' => '1', 'message' => '目录创建失败']);
		}
	}
}