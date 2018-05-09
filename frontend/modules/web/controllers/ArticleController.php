<?php

namespace frontend\modules\web\controllers;

class ArticleController extends \yii\web\Controller
{
    public $layout = '@app/modules/web/views/layouts/main.php';

    public function actionIndex()
    {
        return $this->render('index');
    }

}
