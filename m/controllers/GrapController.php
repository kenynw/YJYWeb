<?php

namespace m\controllers;

use Yii;
use yii\web\Controller;
use common\components\OssUpload;
use QL\QueryList;
use common\models\ProductDetails;
use common\models\ProductRelate;

header("content-type:text/html;charset=utf-8");

//批量处理数据
class GrapController extends Controller
{

    public function init()
    {
        parent::init();
        $this->enableCsrfValidation = false;
        set_time_limit(0);

        if(!isset($_GET['username']) || $_GET['username'] !== 'chenjk'){
            echo "参数错误";
            die;
        }
    }

    //1.批量添加产品
    public function actionIndex(){

        $msg = "";
        if(isset($_POST['page'])){

            $page  = isset($_POST['page']) ? intval($_POST['page']) : 1;
            $pageSize       = isset($_POST['pageSize']) ? intval($_POST['pageSize']) : 250;
            $cateId = isset($_POST['cateId']) ? intval($_POST['cateId']) : "";
            $keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : "";
            $is_dim = isset($_POST['is_dim']) ? trim($_POST['is_dim']) : '0'; //是否模糊匹配 1是，0否

            $t1 = microtime(true);

            for($i = $page;$i <= $pageSize; $i++){
                //产品列表接口
                $keywords = urlencode($keyword);
                $url    = 'https://api.bevol.cn/search/goods/index?keywords='.$keywords.'&category='.$cateId.'&p='.$i;
                $dataArr    = file_get_contents($url);

                if($dataArr){
                    $arr = json_decode($dataArr,true);
                    if($arr['data']['items']){
                        $msg .= self::addProduct($arr['data']['items'],$i,$keyword,$is_dim);
                    }else{
                        break;
                    }
                }
            }

            $t2 = microtime(true);
            $msg .= '程序耗时'.round($t2-$t1,3).'秒';

            return $this->renderPartial('index.htm',[
                'msg'=>$msg,
            ]);

        }

        return $this->renderPartial('index.htm',[
            'msg'=>$msg,
        ]);
    }

    //2.批量添加产品功效
    public function actionAddEffect(){
        $t1 = microtime(true);

        $min_page       = isset($_GET['min_page']) ? intval($_GET['min_page']) : 1;         //最小页数
        $max_page       = isset($_GET['max_page']) ? intval($_GET['max_page']) : isset($_GET['min_page']) ? intval($_GET['min_page']) + 3 : 4;        //最大页数
        $pageSize       = isset($_GET['pageSize']) ? intval($_GET['pageSize']) : 1000;      //一页取多少条
        $orderBy        = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'id';
        $sort           = isset($_GET['sort']) ? $_GET['sort'] : 'asc';
        $created_at     = isset($_GET['created_at']) ? intval($_GET['created_at']) : '';  //只处理新数据
        $id             = isset($_GET['id']) ? intval($_GET['id']) : '';

        $effectSql = "SELECT effect_id,effect_name FROM {{%product_effect}}";
        $effectArr  = Yii::$app->db->createCommand($effectSql)->queryAll();

        $effect     = [];
        foreach ($effectArr as $k => $v) {
            $effect[$v['effect_name']] = $v['effect_id'];
        }

        $whereStr = " 1=1";
        if($created_at) $whereStr .= " AND created_at = '$created_at'";
        if($id) $whereStr .= " AND id = '$id'";

        $compSql    = "SELECT count(id) FROM {{%product_details}} WHERE $whereStr";
        $num  = Yii::$app->db->createCommand($compSql)->queryScalar();
        $count = ceil($num/$pageSize);

        $total = $max_page < $count ? $max_page : $count;

        for($i=$min_page;$i<=$total;$i++){
            $pageMin = ($i - 1) * $pageSize;

            //查产品
            $compSql    = "SELECT id,product_name FROM {{%product_details}} WHERE $whereStr ORDER BY $orderBy $sort limit $pageMin,$pageSize";
            $productArr  = Yii::$app->db->createCommand($compSql)->queryAll();

            $n = 0;
            foreach ($productArr as $key => $value) {
                $id     = $value['id'];
                //查成份
                $compSql= "SELECT C.id,C.component_action
                        FROM {{%product_relate}} R LEFT JOIN {{%product_component}}  C ON  R.component_id = C.id
                        WHERE R.product_id = '$value[id]'";
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
                    $rule6      = preg_match('/黑头/is', $value['product_name']);
                    if($rule6) $effectStr .= $effectStr ? ',6' : '6';
                    //匹配抗痘
                    $rule7      = preg_match('/痘|控油/is', $value['product_name']);
                    if($rule7) $effectStr .= $effectStr ? ',7' : '7';


                    if($effectStr){
                        $sql        = "UPDATE {{%product_details}} SET effect_id = '$effectStr' WHERE id = '$id'";
                        Yii::$app->db->createCommand($sql)->execute();
                    }

                    unset($component);
                    // unset($name);
                    unset($effectStr);
                }
                $n ++;
                usleep(100);
                unset($componentList);
            }

            echo '第' . $i . '页共执行成功'.$n.'条数据，id：' . $productArr[0]['id'] ." --- " . end($productArr)['id'] . '<br/>';
            unset($productArr);
        }

        $t2 = microtime(true);
        echo '程序耗时'.round($t2-$t1,3).'秒';
    }

    //3.批量更新产品评论数
    public function actionUpdateComment(){

        $min_page       = isset($_GET['min_page']) ? intval($_GET['min_page']) : 1;         //最小页数
        $max_page       = isset($_GET['max_page']) ? intval($_GET['max_page']) : 10;        //最大页数
        $pageSize       = isset($_GET['pageSize']) ? intval($_GET['pageSize']) : 1000;      //一页取多少条
        $orderBy        = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'id';
        $sort           = isset($_GET['sort']) ? $_GET['sort'] : 'asc';
        $created_at     = isset($_GET['created_at']) ? intval($_GET['created_at']) : '';  //区分新旧数据
        $id     = isset($_GET['id']) ? intval($_GET['id']) : '';

        $t1 = microtime(true);

        $whereStr = " 1=1";
        if($created_at) $whereStr .= " AND created_at = '$created_at'";
        if($id) $whereStr .= " AND id = '$id'";

        $compSql    = "SELECT count(id) FROM {{%product_details}} WHERE $whereStr";
        $num  = Yii::$app->db->createCommand($compSql)->queryScalar();
        $count = ceil($num/$pageSize);

        $total = $max_page < $count ? $max_page : $count;

        for($i=$min_page;$i<=$total;$i++) {
            $pageMin = ($i - 1) * $pageSize;

            //查产品
            $compSql = "SELECT id FROM {{%product_details}} WHERE $whereStr ORDER BY $orderBy $sort limit $pageMin,$pageSize";
            $productArr = Yii::$app->db->createCommand($compSql)->queryColumn();

            $n = 0;
            foreach ($productArr as $key => $post_id) {
                $updateSql = " UPDATE {{%product_details}} P SET P.comment_num = (SELECT COUNT(id) FROM {{%comment}}  WHERE type = '1' AND post_id = '$post_id' AND status = 1)  WHERE P.id = '$post_id'";
                $return = Yii::$app->db->createCommand($updateSql)->execute();

                $n ++;
                usleep(100);
            }

            echo '第' . $i . '页共执行成功'.$n.'条数据，id：' . $productArr[0] ." --- " . end($productArr) . '<br/>';
            unset($productArr);
        }

        $t2 = microtime(true);
        echo '程序耗时'.round($t2-$t1,3).'秒';
    }

    //4.产品，成分关系表数据处理
    public function actionUpdateRelate(){
        set_time_limit(0);
        $t1 = microtime(true);

        $min_page       = isset($_GET['min_page']) ? intval($_GET['min_page']) : 1;         //最小页数
        $max_page       = isset($_GET['max_page']) ? intval($_GET['max_page']) : isset($_GET['min_page']) ? intval($_GET['min_page']) + 4 : 10;        //最大页数
        $pageSize       = isset($_GET['pageSize']) ? intval($_GET['pageSize']) : 1000;      //一页取多少条
        $orderBy        = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'id';
        $sort           = isset($_GET['sort']) ? $_GET['sort'] : 'asc';
        $isDel          = isset($_GET['isDel']) ? intval($_GET['isDel']) : 0;   //是否删除数据

        $compSql    = "SELECT count(id) FROM {{%product_details}}";
        $num  = Yii::$app->db->createCommand($compSql)->queryScalar();
        $count = ceil($num/$pageSize);
        $total = $max_page < $count ? $max_page : $count;

        self::updateRelate($min_page,$total,$isDel,$pageSize,$orderBy,$sort);

        $t2 = microtime(true);
        echo '程序耗时'.round($t2-$t1,3).'秒';
    }

    //产品，成分关系表数据处理方法
    static function updateRelate($min_page,$total,$isDel,$pageSize,$orderBy,$sort,$list=array()){

        for($i=$min_page;$i<=$total;$i++) {
            $pageMin = ($i - 1) * $pageSize;

            $compSql = "SELECT id FROM {{%product_details}} ORDER BY $orderBy $sort limit $pageMin,$pageSize";
            $productArr = Yii::$app->db->createCommand($compSql)->queryColumn();

            foreach ($productArr as $key => $post_id) {

                $sql = "SELECT t.id FROM(SELECT * FROM {{%product_relate}}  where product_id = '$post_id' ORDER BY id DESC) as t GROUP BY t.component_id HAVING count(*) > 1";
                $list1 = Yii::$app->db->createCommand($sql)->queryColumn();

                if ($list1) {
                    if($isDel){
                        //删除重复数据
                        $del_str = join(",",$list1);
                        $sql    = "DELETE FROM {{%product_relate}} WHERE id in ({$del_str})";
                        $return = Yii::$app->db->createCommand($sql)->execute();
                    }

                    $list = array_merge($list, $list1);
                }
                unset($list1);
            }

            echo '第' . $i . '页 ###id：' . $productArr[0] ." --- " . end($productArr) . '<br/>';
            unset($productArr);
            usleep(100);
        }

        echo "<pre>";
        print_r($list);

        //可能有多个重复数据
        if($list){
            self::updateRelate($min_page,$total,$isDel,$pageSize,$orderBy,$sort);
        }
        return $list;
    }

    //添加产品
    static function addProduct($item,$page,$keyword,$is_dim){

        $msg = "";
        $time = time();
        foreach($item as $value){

            if(!$is_dim && $keyword && addslashes($value['title']) != $keyword) continue;

            //判断产品名是否为空
            if(empty($value['title'])) continue;

            //判断产品是否存在;
            $sql = "SELECT id FROM {{%product_details}} WHERE id = '{$value['id']}' or product_name = '" . addslashes($value['title']) ."'";
            $isExist  = Yii::$app->db->createCommand($sql)->queryScalar();

            if(!$isExist){

                //上传图片
                $image = $path = '';
                if(isset($value['image']) && !empty($value['image'])){
                    $image = $value['image'];

                    //先上传图片
                    $url = file_get_contents("https://img0.bevol.cn/Goods/source/".$image . "@90p");
                    $filename = Yii::$app->basePath . "/web/uploads/" . $image;
                    file_put_contents($filename, $url);

                    //上传到OSS
                    $fullname = Yii::$app->params['environment'] == "Development" ? "cs/uploads/" : "uploads/";
                    $path = "product_img/" . date("Ymd") ."/" .$image;
                    $upload = new OssUpload();
                    $upload->upload($filename,$fullname.$path);

                    //删除图片
                    if(file_exists($filename)) {
                        unlink($filename);
                    }
                }

                $cateList = array(6,7,8,9,10,11,12,13,15,20,47,30,38);
                $productData   = [
                    'id'                      =>  $value['id'],
                    'product_name'          =>  addslashes($value['title']),
                    'alias'                  =>  addslashes($value['alias']),
                    'remark'                 =>  addslashes($value['remark']),
                    'price'                  =>  $value['price'],
                    'product_img'           =>  $path,
                    'form'                   =>  isset($value['capacity']) ? $value['capacity'] : "",
                    'cate_id'                => in_array($value['category'],$cateList) ? $value['category'] : '53',
                    'component_id'          => isset($value['category']) ? $value['category'] : '',    //临时保存分类id
                    'standard_number'      =>  $value['approval'],
                    'has_img'               => $image ? "1" : "0",
                    'page'                   =>  $page,
                    'created_at'            => $time,
                ];

                $newUrl = 'https://www.bevol.cn/product/'.$value['mid'].'.html';
                //采集产品成分列表
                $newData = QueryList::Query($newUrl,array(
                    'product_country'   => array('.approval_box > p:eq(1)','text','-p'),            //生产国
                    'product_company'=> array('.approval_box > p:eq(2)','text','-p'),               //企业
                    'en_product_company'=> array('.approval_box > p:eq(3)','text','-p'),           //企业(英文)
                    'product_date'  => array('#approval_date','text'),                                //批准日期
                    'star'    => array('.cosmetics-info-box > div:eq(0)','html'),                   //星级
                    'component'=> array(".chengfenbiao .table tr > .td1 > a",'href'),               //成分简介
                    'cf'=> array(".chengfenbiao .table tr > .td1 > a",'text'),                      //成分
                    'fx'=> array(".chengfenbiao .table tr > .td2 > span",'text'),                  //安全风险
                    'hx'=> array(".chengfenbiao .table tr > .td3",'html'),                          //活性成分
                    'zd'=> array(".chengfenbiao .table tr > .td4",'html'),                          //致痘风险
                    'sy'=> array(".chengfenbiao .table tr > .td5",'text'),                          //使用目的
                ))->data;

                //产品详情处理
                if($newData) {
                    $temp = array();
                    $temp[] = explode('：', $newData[0]['product_country']);
                    $temp[] = explode('：', $newData[0]['product_company']);
                    $temp[] = explode('：', $newData[0]['en_product_company']);

                    foreach ($temp as $val) {
                        if (preg_match('/生产国/', $val[0], $match)) {
                            $productData["product_country"] = addslashes($val[1]);
                        }
                        if (preg_match('/生产企业/', $val[0], $match)) {
                            $productData["product_company"] = addslashes($val[1]);
                        }
                        if (preg_match('/生产企业英文/', $val[0], $match)) {
                            $productData["en_product_company"] = addslashes($val[1]);
                        }
                    }

                    //星级
                    $xingji = 0;
                    preg_match_all("/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/", $newData[0]['star'], $match);
                    if ($match) {
                        foreach ($match['1'] as $x => $j) {
                            if (strpos($j, 'xiaostar.png')) $xingji++;
                        }
                    }
                    $productData['star'] = $xingji;
                    //日期处理
                    if (isset($newData[0]['product_date'])) {
                        $productData['product_date'] = str_replace(',', '', $newData[0]['product_date']);
                        $productData['product_date'] = preg_match('/([0-9]{10})/', $productData['product_date'], $times) ? $times[1] : 0;
                    } else {
                        $productData['product_date'] = 0;
                    }
                }

                //产品成分处理
                $componentData = [];
                $component_list = array();
                if($newData){
                    foreach ($newData as $k => $v) {

                        //查询是否存在
                        $id = '';
                        if(addslashes($v['cf'])){
                            $sql = "SELECT id FROM yjy_product_component WHERE name = '" . addslashes($v['cf']) ."'";
                            $id  = Yii::$app->db->createCommand($sql)->queryScalar();
                        }

                        if($id){
                            $component_list[] = $id;
                        }else{

                            $componentData  = [
                                'name' => isset($v['cf']) ? addslashes($v['cf']) : '',
                                'risk_grade' => isset($v['fx']) ? $v['fx'] : '',
                                'is_active' => isset($v['hx']) ? 1 : 0,
                                'is_pox' => isset($v['zd']) ? 1 : 0,
                                'component_action' => isset($v['sy']) ? str_replace("\n","，",$v['sy']) : '',
                                'created_at' => $time,
                            ];

                            //成分详情
                            $newUrls = 'https://www.bevol.cn'.$v['component'];
                            $component_info = QueryList::Query($newUrls,array(
                                'ename'   => array('.component-info-title > p:eq(0)','text','-p'),
                                'alias'   => array('.component-info-title > p:eq(1)','text','-p'),
                                'cas'   => array('.component-info-title > p:eq(2)','text','-p'),
                                'description'   => array('.component-info-box > p','text','-p'),
                            ))->data;

                            if($component_info) {
                                $temp = array();
                                $temp[] = explode('：', $component_info[0]['ename']);
                                $temp[] = explode('：', $component_info[0]['alias']);
                                $temp[] = explode('：', $component_info[0]['cas']);

                                foreach ($temp as $val) {
                                    if (preg_match('/英文名（INCI）/', $val[0], $match)) {
                                        $componentData["ename"] = addslashes($val[1]);
                                    }
                                    if (preg_match('/成分别名/', $val[0], $match)) {
                                        $componentData["alias"] = addslashes($val[1]);
                                    }
                                    if (preg_match('/CAS号/', $val[0], $match)) {
                                        $componentData["cas"] = addslashes($val[1]);
                                    }
                                }
                                $componentData["description"] = addslashes($component_info[0]['description']);
                            }

                            //插入成分列表
                            $id = self::pdo_insert('yjy_product_component',$componentData);
                            $component_list[] = $id;
                        }
                    }
                }

                //插入产品详情
                $product_id = self::pdo_insert('yjy_product_details',$productData);

                //产品成分关系
                if($component_list){
                    foreach($component_list as $component_id){
                        $sql = "INSERT INTO {{%product_relate}} (product_id,component_id) VALUES ({$product_id},{$component_id})";
                        Yii::$app->db->createCommand($sql)->execute();
                    }

                    $msg .= "产品id：" . $product_id . " --- 成分id：" . join(",",$component_list) . "<br/>";
                }

                //添加产品功效
                self::AddEffect($product_id);

                unset($productData);
                unset($newData);
                unset($componentData);
                unset($description);
                usleep(100);
            }
        }

        return $msg;
    }

    //添加产品
    static function addProduct1($item,$page,$keyword,$is_dim){

        $msg = "";
        $time = time();
        foreach($item as $value){

            if(!$is_dim && $keyword && addslashes($value['title']) != $keyword) continue;

            //判断产品名是否为空
            if(empty($value['title'])) continue;


            //判断产品是否存在;
            $sql = "SELECT id FROM {{%product_details}} WHERE id = '{$value['id']}' or product_name = '" . addslashes($value['title']) ."'";
            $isExist  = Yii::$app->db->createCommand($sql)->queryScalar();
            if(!$isExist){

                //产品详情接口
                $data = array ('uuid' => '6B33E097-3E3A-4344-8017-A5B79ABEAAA2', 'type' => '0', 'model' => 'iPhone', 'pager' => '1', 'pageSize' => '20', 'v' => '2.9.1', 'o' => 'iOS', 'sys_v' => '10.2', 'id' => '330423');
                $url = "https://api.bevol.cn/goods/info/" . $value['mid'];
                //$html = self::Post($url, $data);
                $header = array('Content-Type: application/x-www-form-urlencoded');
                $html = self::postCurl($url, $data,$header);

                $html = json_decode($html,true);

                //上传图片
                $image = $path = '';
                if(isset($html['result']['goods']['path']) && !empty($html['result']['goods']['path'])){
                    $image = $html['result']['goods']['path'];

                    //先上传图片
                    $url = file_get_contents("https://img0.bevol.cn/Goods/source/".$image . "@90p");
                    $filename = Yii::$app->basePath . "/web/uploads/" . $image;
                    file_put_contents($filename, $url);

                    //上传到OSS
                    $fullname = Yii::$app->params['environment'] == "Development" ? "cs/uploads/" : "uploads/";
                    $path = "product_img/" . date("Ymd") ."/" .$image;
                    $upload = new OssUpload();
                    $upload->upload($filename,$fullname.$path);

                    //删除图片
                    if(file_exists($filename)) {
                        unlink($filename);
                    }
                }

                //产品详情
                $productData = [];
                $cateList = array(6,7,8,9,10,11,12,13,15,20,47,30,38);
                $productData = array(
                    'id'                     => $html['result']['goods']['id'],
                    'product_name'         => addslashes($html['result']['goods']['title']),
                    'alias'                 => addslashes($html['result']['goods']['alias']),
                    'remark'                => addslashes($html['result']['goods']['remark']),
                    'price'                 => $html['result']['goods']['price'],
                    'product_img'          => $path,
                    'form'                  => isset($html['result']['goods']['capacity']) ? $html['result']['goods']['capacity'] : "",
                    'cate_id'               => in_array($html['result']['goods']['category'],$cateList) ? $html['result']['goods']['category'] : '53',
                    'component_id'         => isset($html['result']['goods']['category']) ? $html['result']['goods']['category'] : '',    //临时保存分类id
                    'standard_number'      => $html['result']['goods']['approval'],
                    'product_country'      => $html['result']['goods']['country'],
                    'product_company'      => $html['result']['goods']['company'],
                    'en_product_company'   => $html['result']['goods']['companyEnglish'],
                    'product_date'          => $html['result']['goods']['approvalDate'],
                    'star'                   => $html['result']['safety'][0]['num'],
                    'has_img'               => $image ? "1" : "0",
                    'created_at'            => $time,     //区分旧数据
                    'page'                   =>  $page,
                    'status'                   =>  '1'  //默认上架
                );

                //成分列表
                $componentData = [];
                $component_list = array();
                foreach($html['result']['composition'] as $val){

                    //查询成分是否存在
                    $sql = "SELECT id FROM {{%product_component}} WHERE name = '" . addslashes($val['title']) ."'";
                    $id  = Yii::$app->db->createCommand($sql)->queryScalar();

                    if($id){
                        $component_list[] = $id;
                    }else {
                        $componentData = array(
                            'name' => addslashes($val['title']),
                            'ename' => addslashes($val['english']),
                            'alias' => addslashes($val['otherTitle']),
                            'cas' => $val['cas'],
                            'risk_grade' => $val['safety'],
                            'is_active' => $val['active'] ? 1 : 0,
                            'is_pox' => $val['acneRisk'],
                            'description' => addslashes($val['remark']),
                            'created_at' => $time,
                            'component_action' => str_replace(",", "，", $val['usedTitle']),
                        );

                        //插入成分列表
                        $id = self::pdo_insert( 'yjy_product_component', $componentData);
                        $component_list[] = $id;
                    }

                }

                //插入产品详情
                $product_id = self::pdo_insert('yjy_product_details',$productData);

                //产品成分关系
                if($component_list){
                    foreach($component_list as $component_id){
                        $sql = "INSERT INTO {{%product_relate}} (product_id,component_id) VALUES ({$product_id},{$component_id})";
                        Yii::$app->db->createCommand($sql)->execute();
                    }

                    $msg .= "页数：" . $page . "---产品id：" . $product_id . " --- 成分id：" . join(",",$component_list) . "<br/>";
                }

                //添加产品功效
                self::AddEffect($product_id);

            }
        }

        return $msg;
    }

    //添加产品功效
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

    //添加操作(返回id)
    static function pdo_insert($tablename, $insertsqlarr){

        $insertkeysql = $insertvaluesql = $comma = '';
        foreach ($insertsqlarr as $insert_key => $insert_value) {
            $insertkeysql .= $comma.'`'.$insert_key.'`';
            $insertvaluesql .= $comma.'\''.$insert_value.'\'';
            $comma = ', ';
        }

        $sql = 'INSERT INTO '.$tablename.' ('.$insertkeysql.') VALUES ('.$insertvaluesql.')';
        $result = Yii::$app->db->createCommand($sql)->execute();

        return Yii::$app->db->getLastInsertId();
    }

    //curl 请求
    static function postCurl($url, $option, $header = 0, $type = 'POST') {

        $curl = curl_init (); // 启动一个CURL会话
        curl_setopt ( $curl, CURLOPT_URL, $url ); // 要访问的地址
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE ); // 对认证证书来源的检查
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE ); // 从证书中检查SSL加密算法是否存在
        curl_setopt ( $curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)' ); // 模拟用户使用的浏览器

        if (! empty ( $option )) {
            $options = json_encode ( $option );
            curl_setopt ( $curl, CURLOPT_POSTFIELDS, $options ); // Post提交的数据包
        }
        if (count($header) > 0 && $header) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt ( $curl, CURLOPT_TIMEOUT, 30 ); // 设置超时限制防止死循环
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
        curl_setopt ( $curl, CURLOPT_CUSTOMREQUEST, $type );
        $result = curl_exec ( $curl ); // 执行操作
        curl_close ( $curl ); // 关闭CURL会话
        return $result;
    }

    //模拟post请求
    static function Post($url, $post = null){
        if (is_array($post)){
            ksort($post);
        }
        $context['http'] = array (
            'method'  => 'POST',
            'timeout' => 60000,
            'content' => http_build_query($post, '', '&'),
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        );
        return file_get_contents($url, false, stream_context_create($context));
    }
    
    //批量更新成分产品关系
    public function actionUpdatePrelate(){
    
        $min_page       = isset($_GET['min_page']) ? intval($_GET['min_page']) : 1;         //最小页数
        $max_page       = isset($_GET['max_page']) ? intval($_GET['max_page']) : 10;
        $pageSize       = isset($_GET['pageSize']) ? intval($_GET['pageSize']) : 1000;      //一页取多少条
        $orderBy        = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'product_id';
        $sort           = isset($_GET['sort']) ? $_GET['sort'] : 'asc';
        $created_at     = isset($_GET['created_at']) ? intval($_GET['created_at']) : '';  //区分新旧数据
        $id     = isset($_GET['product_id']) ? intval($_GET['product_id']) : '';
    
        $t1 = microtime(true);
    
        $whereStr = " 1=1";
        if($created_at) $whereStr .= " AND created_at = '$created_at'";
        if($id) $whereStr .= " AND product_id = '$id'";
        
        $relateNum = "SELECT COUNT(pr.id) num FROM (SELECT id FROM {{%product_relate}} WHERE  $whereStr GROUP BY product_id) pr;";
        $num = Yii::$app->db->createCommand($relateNum)->queryScalar();
        $count = intval(ceil($num/$pageSize));

        $total = $max_page < $count ? $max_page : $count;
        for ($i=$min_page;$i<=$total;$i++) {
            $pageMin = ($i - 1) * $pageSize;
            
            //查成分产品
            $relateSql = "SELECT product_id FROM {{%product_relate}} WHERE $whereStr GROUP BY product_id ORDER BY $orderBy $sort limit $pageMin,$pageSize";
            $productArr = Yii::$app->db->createCommand($relateSql)->queryColumn();

            $all = 0;
            $act = 0;

            foreach ($productArr as $key=>$val) {
                if (!ProductDetails::findOne($val)) {
                    ProductRelate::deleteAll("product_id = $val");
                    $act ++;
                    usleep(100);
                }
                $all ++;
                usleep(100);
            }
            
            echo '第' . $i . '页共执行成功'.$all.'条数据，删除'.$act.'组数据，id：' . $productArr[0] ." --- " . end($productArr) . '<br/>';
            unset($productArr);
        }
        
        $t2 = microtime(true);
        echo '程序耗时'.round($t2-$t1,3).'秒';
    }


}
