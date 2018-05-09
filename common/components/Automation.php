<?php
namespace common\components;

use Yii;
use common\functions\Functions;
/**
 * 定时任务类
 */
class Automation{
    /**
     * [定时任务，每小时执行一次]
     */
    static function timingTask1() {
        //在此可以增加定时任务方法
        self::grab();   //抓取更新
    }
    
    /**
     * [抓取更新]
    */
    static function grab(){
        set_time_limit(300);
        $nowTime    = time();
        $weekTime   = date("w");
        $hourTime   = date('H');
        $minute     = date('i');
        $brandId    = 0;
        $dateTime   = date('Y-m-d H:i:s');
        $msg        = 'Start time :'.$dateTime.'|Result:';
        //只在周一至周五，0点到9点执行
        if($weekTime > 5 || $weekTime < 1 || $hourTime > 9) return false;
        //获取个数
        $sql        = 'SELECT COUNT(*) FROM {{%brand}} WHERE status = \'1\'';
        $total      = Yii::$app->db->createCommand($sql)->queryScalar();

        //开始分配任务量
        $page       = ceil($total / 5); 
        $pageMin    = ($weekTime - 1) * $page;

        $sql        = "SELECT id,name,alias FROM {{%brand}} WHERE status = '1' AND is_auto = '1' ORDER BY id LIMIT $pageMin,$page";
        $brandArr   = Yii::$app->db->createCommand($sql)->queryAll();

        //计算当前执行页码
        $page       = $minute == 0 ? 1 : ceil($minute / 5);
        $pageMin    = ($page - 1) * 10;
        $pageMax    = $page * 10;
        foreach ($brandArr as $key => $value) {
            $bit = substr($value['id'],-1);
            $isImplement   =  self::checkAutomation($value['id'],$pageMax);
            if($bit == $hourTime && $isImplement){
                $returnMsg  = '';
                $brandId    = $value['id'];
                //对应时间，执行对应品牌
                $returnMsg .= self::searchProduct($value['name'],$brandId,$pageMin,$pageMax);
                $returnMsg .= $value['alias'] ? self::searchProduct($value['alias'],$brandId,$pageMin,$pageMax):'';
                self::insertAutomation($brandId,$msg.$returnMsg,$pageMax);
                unset($returnMsg);
            }
            unset($bit);
        } 
        return $msg;
    }
    /**
     * [addProduct 搜索产品]
     * @param [type] $keyword [description]
     */
    static function searchProduct($keyword,$brandId,$pageMin,$pageMax){
        if(!$keyword) return 'keyword is empty';
        $msg        = "";
        $msgArr     = [];
        $time       = time();
        $insertNum  = 0;
        $minute     = date('i');

        for ($i = $pageMin; $i <= $pageMax ; $i++) {
            $url        = 'https://api.bevol.cn/search/goods/index?keywords=' . urlencode($keyword) . '&p='.$i;
            $dataArr    = Functions::http_judu($url,[],'post');
            $arr        = !empty($dataArr) ? json_decode($dataArr,true) :'';

            //开始采集
            if($arr['data']['items']){
                $insertNum += self::addProduct($arr['data']['items'],$brandId,$keyword);
            }else{
                return 'termination|';
                break;
            }
            unset($url);
            unset($dataArr);
        }
        return $insertNum ? 'insert num '.$insertNum : ' There is no new addition';
    }
    /**
     * [addProduct 添加产品]
     * @param [type] $item    [description]
     */
    static function addProduct($item,$brandId,$keyword){
        $num    = 0;
        $time   = time();
        $minTime= '1511971200';

        foreach($item as $value){
            //判断产品名是否为空
            if(empty($value['title'])) continue;
            //精确查找
            $rule    = '/'.strtolower($keyword) .'/';
            $isPreg  = preg_match($rule,strtolower($value['title'])); 

            if(!$isPreg) continue;

            //判断产品是否存在;
            $sql            = "SELECT id,brand_id,product_img,created_at FROM {{%product_details}} WHERE product_name = '" . addslashes($value['title']) ."'";
            $productInfo    = Yii::$app->db->createCommand($sql)->queryOne();
            //存在则不入库
            if(!empty($productInfo)) {
                if($productInfo['brand_id'] != $brandId){
                    $updateSql   = "UPDATE {{%product_details}} SET brand_id = '$brandId' WHERE id = '$productInfo[id]'";
                    Yii::$app->db->createCommand($updateSql)->execute();
                }
                continue;   
            }
            $product_id     = $productInfo ? $productInfo['id'] : '';
            $product_img    = $productInfo ? $productInfo['product_img'] : '';
            $createImageTime= $productInfo ? $productInfo['created_at'] : 0;

            $dataJson = Functions::http_judu('https://api.bevol.cn/entity/info2/goods',['mid'=>$value['mid']],'post');
            $newData  = json_decode($dataJson,true);
            $newData  = $newData['ret'] == 0 ? $newData['result'] : '';
            //产品成分处理
            $componentData  = [];
            $component_list = [];
            //上传图片
            $filename    =  $product_img;
            if(!empty($value['image'])){
                if($createImageTime == 0 || $createImageTime >= $minTime){
                    $url      = 'https://img0.bevol.cn/Goods/source/'.$value['image'] . '@90p';
                    $filename =  Functions::uploadUrlimg($url,'product_img');
                    unset($url);
                }
            }

            $productData   = [
                'has_price'       =>  $value['price'] ? "1" : "0",
                'is_complete'     =>  $value['price'] && $filename ? '1' : '0',
                'has_img'         =>  $filename ? "1" : "0",
                'product_img'     =>  $filename,
                'standard_number' =>  $value['approval'],
                'updated_at'      =>  $time
            ];
            if(!$newData || !isset($newData['entityInfo']['composition'])) continue;

            foreach ($newData['entityInfo']['composition'] as $k => $v) {
                //查询是否存在
                $component_id = '';
                $sql            = "SELECT id FROM yjy_product_component WHERE name = '" . addslashes($v['title']) ."'";
                $component_id   = Yii::$app->db->createCommand($sql)->queryScalar();
                if(!$component_id){
                    $componentData  = [
                        'name' => addslashes($v['title']),
                        'ename'=> $v['english'],
                        'cas'  => $v['cas'],
                        'alias'=> $v['otherTitle'],
                        'risk_grade'=> $v['safety'],
                        'is_active' => $v['active']   ? 1 : 0,
                        'is_pox'    => $v['acneRisk'] ? 1 : 0,
                        'component_action' => $v['usedTitle'],
                        'description' => $v['remark'],
                        'created_at'  => $time,
                    ];
                    $component_id = self::pdo_insert('yjy_product_component',$componentData,$component_id);
                }
                //插入成分列表
                $component_list[] = $component_id;
            }
            //生产国
            $productData["product_country"] = $newData['entityInfo']['goods']['country'];
            $productData["product_company"] = $newData['entityInfo']['goods']['company'];
            $productData["en_product_company"] = $newData['entityInfo']['goods']['companyEnglish'];
            $productData["product_date"] = $newData['entityInfo']['goods']['approvalDate'];
            

            //存在更新，不存在入库
            // if($product_id){
            //     $product_id = self::pdo_insert('yjy_product_details',$productData,$product_id);
            //     //先删除关联数据
            //     $sql = "DELETE FROM {{%product_relate}} WHERE product_id='$product_id'";
            //     Yii::$app->db->createCommand($sql)->execute();
            //     //添加成分
            //     if($component_list){
            //         foreach($component_list as $component_id) {
            //             if($component_id){
            //                 $sql = "INSERT INTO {{%product_relate}} (product_id,component_id) VALUES ({$product_id},{$component_id})";
            //                 Yii::$app->db->createCommand($sql)->execute();
            //             }
            //         }
            //     }
            //     $msg .= "Update id：" . $product_id . "<br/>";
            // }else{
                //处理分类
                if(isset($value['category'])){
                    if($value['category'] == 12 || $value['category'] == 15){
                        $value['category'] = 6;
                    }else if($value['category'] == 30 || $value['category'] == 47){
                        $value['category'] = 38;
                    }else if($value['category'] == 20){
                        $value['category'] = 13;
                    }
                }

                $cateList = array(6,7,8,9,10,11,12,13,15,20,47,30,38);
                $productData   = [
                    'id'                =>  $value['id'],
                    'product_name'      =>  addslashes($value['title']),
                    'alias'             =>  addslashes($value['alias']),
                    'brand_id'          =>  $brandId,
                    'remark'            =>  addslashes($value['remark']),
                    'price'             =>  $value['price'],
                    'product_img'       =>  $filename,
                    'form'              =>  isset($value['capacity']) ? $value['capacity'] : "",
                    'cate_id'           =>  in_array($value['category'],$cateList) ? $value['category'] : '53',
                    'standard_number'   =>  $value['approval'],
                    'has_img'           =>  $filename ? "1" : "0",
                    'has_price'         =>  $value['price'] ? "1" : "0",
                    'is_complete'       =>  $value['price'] && $filename ? '1' : '0',
                    'star'              =>  $newData['entity']['safety_1_num'],
                    'created_at'        =>  $time,
                ];
                if($newData){
                    //生产国
                    $productData["product_country"] = $newData['entityInfo']['goods']['country'];
                    $productData["product_company"] = $newData['entityInfo']['goods']['company'];
                    $productData["en_product_company"] = $newData['entityInfo']['goods']['companyEnglish'];
                    $productData["product_date"] = $newData['entityInfo']['goods']['approvalDate'];
                }
                //产品成分关系
                $product_id = self::pdo_insert('yjy_product_details',$productData);

                if($component_list){
                    foreach($component_list as $component_id){
                        if($component_id){
                            $sql = "INSERT INTO {{%product_relate}} (product_id,component_id) VALUES ({$product_id},{$component_id})";
                            Yii::$app->db->createCommand($sql)->execute();
                        }
                    }
                }
                unset($filename);
            // }
            $num++;
            //添加产品功效
            self::AddEffect($product_id);
            //记录产品日志
            self::insertAutomationProduct($product_id,$brandId);

            unset($productData);
            unset($newData);
            unset($componentData);
            unset($component_list);
            usleep(100);
        }
        return $num;
    }
    /**
     * [AddEffect 添加产品功效]
     * @param [type] $id [description]
     */
    static function AddEffect($id){
        if(empty($id)) return false;

        $effectSql = "SELECT effect_id,effect_name FROM {{%product_effect}}";
        $effectArr  = Yii::$app->db->createCommand($effectSql)->queryAll();
        $effect     = [];
        foreach ($effectArr as $k => $v) {
            $effect[$v['effect_name']] = $v['effect_id'];
        }

        //查产品
        $compSql    = "SELECT product_name FROM {{%product_details}} WHERE id = '$id'";
        $product_name  = Yii::$app->db->createCommand($compSql)->queryScalar();

        //查成份
        $compSql= "SELECT C.id,C.component_action
                FROM {{%product_relate}} R LEFT JOIN {{%product_component}}  C ON  R.component_id = C.id
                WHERE R.product_id = '$id'";
        $componentList  = Yii::$app->db->createCommand($compSql)->queryAll();

        //功效成份
        foreach ($componentList as $k => $v) {
            $component  = $v['component_action'];
            // $name       = $v['name'];
            $effectStr  = '';
            //匹配美白
            $rule1      = preg_match('/美白祛斑/is', $component);
            if($rule1) $effectStr .= $effectStr ? ',1' : '1';
            //匹配保湿
            $rule2      = preg_match('/保湿剂/is', $component);
            if($rule2) $effectStr .= $effectStr ? ',2' : '2';
            //匹配舒缓抗敏
            $rule3      = preg_match('/舒缓抗敏/is', $component);
            if($rule3) $effectStr .= $effectStr ? ',3' : '3';
            //匹配去角质
            $rule4      = preg_match('/去角质/is', $component);
            if($rule4) $effectStr .= $effectStr ? ',4' : '4';
            //匹配去抗皱
            $rule5      = preg_match('/抗氧化剂/is', $component);
            if($rule5) $effectStr .= $effectStr ? ',5' : '5';
            //匹配去黑头
            $rule6      = preg_match('/黑头/is', $product_name);
            if($rule6) $effectStr .= $effectStr ? ',6' : '6';
            //匹配抗痘
            $rule7      = preg_match('/痘|控油/is', $product_name);
            if($rule7) $effectStr .= $effectStr ? ',7' : '7';

            if($effectStr){
                $sql        = "UPDATE {{%product_details}} SET effect_id = '$effectStr' WHERE id = '$id'";
                Yii::$app->db->createCommand($sql)->execute();
            }
        }
        usleep(100);
        unset($componentList);
    }
    /**
     * [pdo_insert 添加更新产品]
     * @param  [type] $tablename    [description]
     * @param  [type] $insertsqlarr [description]
     * @param  string $id           [description]
     * @return [type]               [description]
     */
    static function pdo_insert($tablename, $insertsqlarr,$id=''){

        if($id){
            $update_data = "";
            foreach ($insertsqlarr as $key => $val) {
                $update_data .= $key . "='".$val . "',";
            }
            $update_data = trim($update_data,",");
            
            if ($tablename == 'yjy_product_details') {
                $time = time();
                $update_data .= ",updated_at = $time";
            }
            
            $sql = "UPDATE $tablename SET {$update_data} WHERE id=$id";
            $result = Yii::$app->db->createCommand($sql)->execute();

            return $id;
        }else{
            $insertkeysql = $insertvaluesql = $comma = '';
            foreach ($insertsqlarr as $insert_key => $insert_value) {
                $insertkeysql .= $comma.'`'.$insert_key.'`';
                $insertvaluesql .= $comma.'\''.$insert_value.'\'';
                $comma = ', ';
            }
            $sql = 'INSERT IGNORE INTO '.$tablename.' ('.$insertkeysql.') VALUES ('.$insertvaluesql.')';
            $result = Yii::$app->db->createCommand($sql)->execute();

            return Yii::$app->db->getLastInsertId();
        }

    }
    /**
     * [checkAutomation 验证是否已执行]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    static function checkAutomation($id,$pageMax){
        $id         = intval($id);
        $pageMax    = intval($pageMax);
        $time       = date('Y-m-d');
        $sql    = "SELECT id FROM {{%log_automation}} WHERE brand_id = '$id' AND add_time = '$time' AND page = '$pageMax'";
        $isTure = Yii::$app->db->createCommand($sql)->queryScalar();

        return $isTure ? false : true;
    }
    /**
     * [insertAutomation 记录日志]
     * @param  [type] $id [description]
     * @param  [type] $msg [description]
     * @return [type]     [description]
     */
    static function insertAutomation($id,$msg,$pageMax){
        $id         = intval($id);
        $pageMax    = intval($pageMax);
        $time       = date('Y-m-d');
        $nowTime    = time();

        $sql    = "INSERT INTO  {{%log_automation}}(brand_id,`msg`,add_time,`page`,created_at) VALUES('$id','$msg','$time','$pageMax','$nowTime')";
        Yii::$app->db->createCommand($sql)->execute();
    }
    /**
     * [insertAutomationProduct 记录产品日志]
     * @param  [type] $productId [description]
     * @param  [type] $brandId [description]
     * @return [type]     [description]
     */
    static function insertAutomationProduct($productId,$brandId){
        $productId  = intval($productId);
        $brandId    = intval($brandId);
        $time       = date('Y-m-d');
        $nowTime    = time();

        if(empty($productId)) return false;
        $sql    = "INSERT INTO  {{%log_automation_product}}(product_id,brand_id,add_time,created_at) VALUES('$productId','$brandId','$time','$nowTime')";
        Yii::$app->db->createCommand($sql)->execute();
    }
}
?>