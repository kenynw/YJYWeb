<?php
namespace backend\models;
use yii;
use yii\base\Model;
use common\functions\Tools;
use common\models\CommonTag;
use yii\base\Object;
use common\models\ProductCategory;
use common\models\ProductDetails;
use yii\helpers\Url;
use common\components\OssUpload;
use common\models\Comment;
use common\models\Topic;

/**
 * common functions
 */
class CommonFun extends Model 
{
    
    //获取同个表的的两个字段的键与键值对应关系的数组
    public static function getKeyValArr($model,$id,$name,$where = '') 
    {
        $modelArr = $model->find()
        ->select("$id,$name")
        ->where("$where")
        ->asArray()
        ->all();
        $return = [];
        foreach ($modelArr as $key=>$val) {
            $return[$val[$id]] = $val[$name];
        }
        return $return;
    }
    
    //获取两个表关联关系的两个字段的键与键值对应关系的数组
    public static function getConnectArr($arr,$connectModel,$id,$name)
    {
        $val = [];
        $return = [];
        foreach ($arr as $key=>$val) {
            $modelArr = $connectModel->find()
                ->select("$id,$name")
                ->where("$id = $val")
                ->asArray()
                ->one();
            $return[$val] = $modelArr[$name];
        }
        return $return;
    }
    
    
    
    
    
    
    
    
    
    
    //报错
    public function notFound ($content){
        $class = $this->context->id;
        $action = $this->context->action->id; 
        throw new \yii\web\NotFoundHttpException("控制器：$class--方法：$action.出错：$content");
    }
    
    //更新标签数据
    public static function updateCommonTag (){
        $commonTagArr = CommonTag::find()->asArray()->all();
        foreach ($commonTagArr as $key=>$val) {
            $tagUpdateSql  = "UPDATE {{%common_tag}} C SET C.count = (SELECT COUNT(*)  FROM {{%common_tagitem}}  WHERE  tagid = '{$val['tagid']}') WHERE C.tagid = '{$val['tagid']}'";
            Yii::$app->db->createCommand($tagUpdateSql)->execute();
    
            $count = CommonTag::find()->where("tagid = {$val['tagid']} AND type = '2'")->asArray()->all();
            if (!empty($count)) {
                foreach ($count as $key2=>$val2) {
                    $val2['count'] == '0' ? CommonTag::deleteAll(['tagid' => $val2['tagid']]) : false;
                }
            }
        }
    }
    
    //获取分类
    public static function getCateList ($where=''){
//         $cateid = ProductCategory::find()->where('parent_id <> 0')->asArray()->all();
//         $cateList = [];
//         foreach ($cateid as $key=>$val) {
//             $cateList[$val['id']] = $val['cate_name'];
//         }
        
        return yii\helpers\ArrayHelper::map(ProductCategory::find()->where($where)->all(), 'id', 'cate_name');
    }
    
    //话题
    public static function getTopicList ($where=''){    
        return yii\helpers\ArrayHelper::map(Topic::find()->where($where)->all(), 'id', 'title');
    }
    
    //更新品牌排行榜顺序
    public static function updateProductRank ($brand_id){
        if ($brand_id) {
            $product = ProductDetails::find()
            ->where("brand_id = $brand_id AND is_top = 1")
            ->orderBy('comment_num DESC,star DESC')
            ->asArray()
            ->all();//var_dump($product);die;
            if (!empty($product)) {
                foreach ($product as $key=>$val) {
                    $rank = $key+1;
                    $id = $val['id'];
                    $update = "UPDATE {{%product_details}} SET ranking = $rank WHERE id = $id";
                    Yii::$app->db->createCommand($update)->execute();
                }
            }
        }
    }
    
    //添加详情操作记录
    public static function addAdminLogView ($description='',$relate_id=''){
        $route = Url::to();
        $user_id = Yii::$app->user->id;
        $userName = Yii::$app->user->identity->username;
        $action = Yii::$app->controller->id;
        $time = time();
        
        $sql = "INSERT INTO yjy_admin_log_view (route,user_id,username,relate_id,description,action,created_at) VALUES ('$route','$user_id','$userName','$relate_id','$description','$action','$time')";
        Yii::$app->db->createCommand($sql)->execute();
    }
    
    //上传多张图片
    public static function uploadImg ($imgFilename){
        $base64_url = Yii::$app->request->post('base64');
        $base64_body = substr(strstr($base64_url,','),1);
        $imgType = explode('/', explode(';', $base64_url)[0])[1];
        if (empty($imgType)) {
            $imgType = 'jpeg';
        }        
        $imgData = base64_decode($base64_body);
        
        $serverName =  Yii::$app->params['isOnline'];
        if($serverName){
            $savePath   =   'uploads';
        }else{
            $savePath   =   'cs/uploads';
        }
        $img_name   =   $imgFilename.'/'.date('Ymd').'/'.time().rand(100000,999999).".".$imgType;
        $filename   =   $savePath.'/'.$img_name;
        
        $dirname    =   dirname($filename);       
        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
            return false;
        }
        
        file_put_contents($filename,$imgData);
        $oss_obj = new OssUpload();
        $is_upload = $oss_obj->upload($filename,$filename);
        if($is_upload){
            return ['status' => '1', 'message' => 'success', 'filename' => $img_name];
        }
    }
    
    //获取管理员的相关马甲（评论、点赞）
    public static function getShadowList()
    {
        //缓存
        if (Yii::$app->cache->get('ShadowList')) {
            $userList2 = Yii::$app->cache->get('ShadowList');
        } else {
            $adminId = Yii::$app->user->identity;
            $userList = \common\models\User::find()->select('id,username,birth_year')->where('status <> 0')->andWhere('admin_id != 0')->asArray()->all();
            
            //         if($userList){
            //             foreach ($userList as $key=>$val) {
            //                 $userList[$key]['comment_time'] = Comment::find()->select("created_at")->where(['user_id' => $val['id']])->orderBy("id desc")->scalar();
            //             }
            //         }
            
            //         foreach ( $userList as $key => $row ){
            //             $comment_time[$key] = $row ['comment_time'];
            //         }
            
            //         array_multisort($comment_time, SORT_DESC, $userList);
            
            $userList2 = [];
            foreach ($userList as $key=>$val) {
            
                //$time = $val['comment_time'] ? " - - - 最新评论时间：" . date("Y-m-d H:i:s",$val['comment_time']) : "";
            
                $age = $val['birth_year'] ? " - - - 年龄：" . (date("Y") - $val['birth_year']) : "";
            
                //用户所属肤质
                $sql = "SELECT s.id,s.skin,s.explain FROM {{%user_skin}} us LEFT JOIN {{%skin}} s on us.skin_name=s.skin  WHERE uid = '{$val['id']}'";
                $skin_info  = Yii::$app->db->createCommand($sql)->queryOne();
                $skin = $skin_info['skin'] ? " - - - 肤质：" . $skin_info['skin'].'('.$skin_info['explain'].')' : "";
            
                $userList2[$val['id']] = "马甲名：" . Tools::userTextDecode($val['username']) . $skin . $age;
            }
            Yii::$app->cache->set('ShadowList', $userList2,604800);
        }

        return $userList2;
    }
    

}