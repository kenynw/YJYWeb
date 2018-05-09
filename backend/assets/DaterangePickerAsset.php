<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DaterangePickerAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte/plugins/';

    public $css = [
        'daterangepicker/daterangepicker-bs3.css',
    ];
    public $js = [
    	'daterangepicker/moment.js',
        'daterangepicker/daterangepicker.js',
    ];
    public $depends = [
        
    ];
}