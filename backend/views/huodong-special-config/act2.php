<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = "参与情况统计结果";
$this->params['breadcrumbs'][] = ['label' => '抽奖活动列表', 'url' => ['index']];
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
                <td></td><th colspan="6" style="font-size: 20px">活动参与情况统计</th><th rowspan="3" style=" vertical-align: middle">未登录分享次数</th>
            </tr>
            <tr>
                <td></td><th colspan="3">老用户（注册时间为8月2号之前的）</th><th colspan="3">新用户（注册时间为8月2号之后的）</th>
            </tr>
            <tr>
                <td></td>
                <th style='cursor:pointer;color:#3c8dbc' title='点击文章的人数(已去重)'>app文章阅读人数</th>
                <th style='cursor:pointer;color:#3c8dbc' title='分享文章的次数(已登录)'>分享次数(ios)</th>
                <th style='cursor:pointer;color:#3c8dbc' title='分享文章的次数(已登录)'>分享次数(android)</th>
                <th style='cursor:pointer;color:#3c8dbc' title='点击文章的人数(已去重)'>app文章阅读人数</th>
                <th style='cursor:pointer;color:#3c8dbc' title='分享文章的次数(已登录)'>分享次数(ios)</th>
                <th style='cursor:pointer;color:#3c8dbc' title='分享文章的次数(已登录)'>分享次数(android)</th>
            </tr>
            <?php foreach ($participateArr as $key=>$val) { ?>
            <tr>
                <td width="10%">第<?=$key+1 ?>天</td><td><?=$val['articleNum']['old'] ?></td><td><?=$val['shareNum']['old']['ios'] ?></td><td><?=$val['shareNum']['old']['android'] ?></td><td><?=$val['articleNum']['new'] ?></td><td><?=$val['shareNum']['new']['ios'] ?></td><td><?=$val['shareNum']['new']['android'] ?></td><td><?=$val['shareNum']['guest'] ?></td>
            </tr>
            <?php } ?>
            <?php 
                $article_sum_new = 0;$article_sum_old = 0;
                $share_sum_new_ios = 0;$share_sum_old_ios = 0;
                $share_sum_new_android = 0;$share_sum_old_android = 0;
                $share_sum_guest = 0;
                foreach ($participateArr as $key=>$val) { 
                    $article_sum_new += $val['articleNum']['new'];$article_sum_old += $val['articleNum']['old'];
                    $share_sum_old_ios += $val['shareNum']['old']['ios'];$share_sum_old_android += $val['shareNum']['old']['android'];
                    $share_sum_new_ios += $val['shareNum']['new']['ios'];$share_sum_new_android += $val['shareNum']['new']['android'];
                    $share_sum_guest += $val['shareNum']['guest'];
                } ?>
            <tr>
                <td width="10%">总数</td><td><?=$article_sum_old ?></td><td><?=$share_sum_old_ios ?></td><td><?=$share_sum_old_android ?></td><td><?=$article_sum_new ?></td><td><?=$share_sum_new_ios ?></td><td><?=$share_sum_new_android ?></td><td><?=$share_sum_guest ?></td>
            </tr>
        </table>
    </div>
    
    <!-- 下载按钮点击次数 -->
    <br>
    <div class="download"> 
        <table  class="table table-striped table-bordered">
            <tr><th colspan="3"  style="font-size: 20px">下载按钮点击次数</th></tr>
            <tr><th></th><th>下载（h5文章页）</th><th>立即下载，开始变美（h5下载页）</th></tr> 
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
    
    <!-- 符合抽奖条件的用户名单 -->
    <br>
    <div class="draw"> 
        <table  class="table table-striped table-bordered">
            <tr><th colspan="3"  style="font-size: 20px">符合抽奖条件的用户名单(<?=count($draw) ?>人)</th></tr>
            <tr><th>用户名</th><th>颜值</th><th>获取资格时间</th></tr> 
            <?php foreach ($draw as $key=>$val) {?>
            <tr><td><a href="/user/view?id=<?=$val['id'] ?>" style="color:green" data-toggle="tooltip" data-placement="left" target="_blank" data-original-title="真实用户"><?=$val['user'] ?></a></td><td><?=$val['rank'] ?></td><td><?=$val['time'] ?></td></tr> 
            <?php } ?>          
        </table>
    </div>   
    
</div>
