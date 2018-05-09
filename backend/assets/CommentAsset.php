<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class CommentAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
    ];
    public $js = [
        'js/emotions.js',
        'js/sinaFaceAndEffec.js',
        'js/artDialog4.1.6/jquery.artDialog.source.js?skin=idialog',
        'js/artDialog4.1.6/plugins/iframeTools.source.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
