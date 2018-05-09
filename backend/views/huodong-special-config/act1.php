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
                <th style='cursor:pointer;color:#3c8dbc' title='app活动页的点击数（用户id已去重）'>查看活动用户数</th>
                <th style='cursor:pointer;color:#3c8dbc' title='在app活动页发起邀请操作的用户数'>发起邀请用户数</th>
                <th style='cursor:pointer;color:#3c8dbc' title='参与助攻的人数'>助攻次数</th>
                <th style='cursor:pointer;color:#3c8dbc' title='app活动页的点击数（用户id已去重）'>查看活动用户数</th>
                <th style='cursor:pointer;color:#3c8dbc' title='在app活动页发起邀请操作的用户数'>发起邀请用户数</th>
                <th style='cursor:pointer;color:#3c8dbc' title='参与助攻的人数'>助攻次数</th>
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
    
    <!-- 活动成本监控 -->
    <br>
    <div class=""> 
        
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

</div>
