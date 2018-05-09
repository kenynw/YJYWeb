<?php
namespace api\controllers;

use yii;
use yii\web\Controller;
use common\components\Automation;

class AutomationController extends Controller
{
    public function actionGrab(){
        //定时任务调用--每小时
        Automation::timingTask1();
    }
}
?>