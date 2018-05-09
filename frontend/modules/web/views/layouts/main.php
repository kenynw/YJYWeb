<?php

use yii\helpers\Html;
use frontend\assets\AppAsset;

AppAsset::register($this);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="apple-mobile-web-app-title" content="<?= Html::encode($this->title) ?>"/>
    <meta name="author" content="chendianhuai,XiaMen,">
    <meta name="date" content="<?= date('Y-m-d H:m:s') ?>">
    <meta name="copyright" content="Copyright (c) 2016 www.miguantech.com.">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no"/>
    <meta name="MobileOptimized" content="320">
    <meta name="format-detection" content="telephone=no, address=no, email=no">
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-touch-fullscreen" content="yes"/>
    <title><?= Html::encode($this->title) ?></title>
    <link rel="apple-touch-icon-precomposed" href=""/>


</head>

<?= $content ?>

</html>
 