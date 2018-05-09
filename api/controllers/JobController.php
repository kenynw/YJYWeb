<?php
namespace api\controllers;

use yii;
use yii\web\Controller;
use common\components\Timingtaskcom;

class JobController extends Controller
{
    public function actionHour(){
        //定时任务调用--每小时
        TimingTaskCom::timingTask1();
    }
    public function actionDay(){
        //定时任务调用--每天
        TimingTaskCom::timingTask2();
    }
    public function actionReport(){
        //定时任务调用--每天23:59:59
        TimingTaskCom::timingTask3();
    }
    public function actionMonth(){
        //定时任务调用--每月1号0点执行
        TimingTaskCom::timingTask4();
    }
    public function actionDaily(){
        //定时任务调用--每天
       TimingTaskCom::timingTask5();

    }
}
?>