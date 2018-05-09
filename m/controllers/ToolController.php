<?php
namespace m\controllers;

use yii;
use common\models\User;
use common\functions\Cosmetics;
/**
 * 工具方法
 */
class ToolController extends BaseController
{
    public function actions(){
        $this->GLOBALS['TOP_SHOW'] = 'tool';
    }
    /**
     * 化妆品工具页面
    */
    public function actionCosmetics()
    {
        //需要登录就调用该方法
        // $this->wxLogin();
        //品牌列表
        $cosmeticsList  = Cosmetics::iosCosmeticsList_v3(1); 
        // $hotCosmetics   = $cosmeticsList;
        // $sort = array(  
        //     'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序  
        //     'field'     => 'num',       //排序字段  
        // );  
        // $arrSort = array();  
        // foreach($cosmeticsList AS $uniqid => $row){  
        //     foreach($row AS $key=>$value){  
        //         $arrSort[$key][$uniqid] = $value;  
        //     }  
        // }  
        // if($sort['direction']){  
        //     array_multisort($arrSort[$sort['field']], constant($sort['direction']), $hotCosmetics);  
        // }
        //分享
        $this->GLOBALS['OfficialAccounts']['share']['title'] = "在线批号查询工具";
        $this->GLOBALS['OfficialAccounts']['share']['desc']  = "记录保质期、开封日期，过期提醒";

        return $this->renderPartial('index.htm',[
            'cosmeticsList' =>  $cosmeticsList,
            'GLOBALS'       =>  $this->GLOBALS,
            'title'         =>  '化妆品查询工具-颜究院',
        ]);
    }
    /**
     * 化妆品查询方法
    */
    public function actionSelectCosmetics()
    {
        $id     = Yii::$app->request->get('id');
        $number = Yii::$app->request->get('number');
        $number = strtoupper(trim($number));
        $number = str_replace(' ', '', $number);

        //品牌列表
        $sql      = "SELECT rule FROM {{%brand}} WHERE id = '$id'";
        $rule     = Yii::$app->db->createCommand($sql)->queryScalar();

        if(!$rule){
            $return = ['status' => -1, 'msg' => '品牌规则不存在'];
            return  json_encode($return);
        }

        $fun = 'rule'.$rule;
        $return = Cosmetics::$fun($number);

        $sql = "UPDATE {{%cosmetics_tool}} SET num = num + 1 WHERE id = '$id'";
        Yii::$app->db->createCommand($sql)->execute();

        return json_encode($return);
          
    }
}