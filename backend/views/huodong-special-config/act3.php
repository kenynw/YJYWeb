<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = "($model->name)参与情况统计结果";
$this->params['breadcrumbs'][] = ['label' => '助攻活动列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
    table td,th{text-align: center;}
    .table-bordered{border:12px solid #f4f4f4;}
</style>

<div class="huodong-special-config-view">
    <!-- 活动参与情况统计 -->
    <div class="participate"> 
        <table class="table table-striped table-bordered">
            <tr>
                <td></td><th colspan="6" style="font-size: 20px">活动参与情况统计</th>
            </tr>
            <tr>
                <td></td><th colspan="3">新用户</th><th colspan="3">老用户</th>
            </tr>
            <tr>
                <td></td>
                <th style='cursor:pointer;color:#3c8dbc' title='【app内的】活动页查看人数（活动期间，该数据已去重）'>查看活动人数</th>
                <th style='cursor:pointer;color:#3c8dbc' title='【app内的】点击免费领取并分享邀请链接的人数（活动期间，该数据已去重）'>发起邀请人数</th>
                <th style='cursor:pointer;color:#3c8dbc' title='【助攻页的】点击助攻并提示成功助攻的次数'>被助攻次数</th>
                <th style='cursor:pointer;color:#3c8dbc' title='【app内的】活动页查看人数（活动期间，该数据已去重）'>查看活动人数</th>
                <th style='cursor:pointer;color:#3c8dbc' title='【app内的】点击免费领取并分享邀请链接的人数（活动期间，该数据已去重）'>发起邀请人数</th>
                <th style='cursor:pointer;color:#3c8dbc' title='【助攻页的】点击助攻并提示成功助攻的次数'>被助攻次数</th>
            </tr>
            <?php foreach ($participateArr as $key=>$val) { ?>
            <tr>
                <td width="10%">第<?=$key+1 ?>天</td><td><?=$val['click']['new'] ?></td><td><?=$val['draw']['new'] ?></td><td><?=$val['invite']['new'] ?></td><td><?=$val['click']['old'] ?></td><td><?=$val['draw']['old'] ?></td><td><?=$val['invite']['old'] ?></td>
            </tr>
            <?php } ?>
            <?php 
                $click_sum_new = 0;$click_sum_old = 0;
                $draw_sum_new = 0;$draw_sum_old = 0;
                $invite_sum_new = 0; $invite_sum_old = 0;
                foreach ($participateArr as $key=>$val) { 
                    $click_sum_new += $val['click']['new'];$click_sum_old += $val['click']['old'];
                    $draw_sum_new += $val['draw']['new'];$draw_sum_old += $val['draw']['old'];
                    $invite_sum_new += $val['invite']['new'];$invite_sum_old += $val['invite']['old'];
                } ?>
            <tr>
                <td width="10%">总数</td><td><?=$click_sum_new ?></td><td><?=$draw_sum_new ?></td><td><?=$invite_sum_new ?></td><td><?=$click_sum_old ?></td><td><?=$draw_sum_old ?></td><td><?=$invite_sum_old ?></td>
            </tr>
        </table>
    </div>
    
    <!-- 助攻人数所占比例 -->
    <div class="invite"> 
        <table  class="table table-striped table-bordered">
            <tr><th colspan="2"  style="font-size: 20px">助攻人数所占比例</th></tr>
            <?php foreach ($inviteArr as $key=>$val) {?>
                <tr><td width="50%"><?=$key ?>人</td><td><?=empty($val) ? $val : $val.'%' ?></td></tr>
            <?php } ?>
        </table>
    </div>
    
    <!-- 下载按钮点击次数 -->
    <br>
    <div class="download"> 
        <table  class="table table-striped table-bordered">
            <tr><th colspan="3"  style="font-size: 20px">下载按钮点击次数</th></tr>
            <tr><th></th><th>去下载（助攻页）</th><th>立即下载，开始变美（h5下载页）</th></tr> 
            <?php foreach ($downloadArr as $key=>$val) {?>
            <tr><td>第<?=$key+1 ?>天</td><td><?=$val['type1'] ?></td><td><?=$val['type2'] ?></td></tr> 
            <?php } ?>
            <?php 
                $type1_sum = 0;$type2_sum = 0;
                foreach ($downloadArr as $key=>$val) { 
                    $type1_sum += $val['type1'];$type2_sum += $val['type2'];
                } ?>
            <tr>
                <td width="10%">总数</td><td><?=$type1_sum ?></td><td><?=$type2_sum ?></td>
            </tr>          
        </table>
    </div>

</div>
