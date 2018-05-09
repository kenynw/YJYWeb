<?php

namespace frontend\modules\h5\controllers;

use common\models\Article;
use yii\base\Object;
use frontend\models\WebPage;

class ArticleController extends BaseController
{
    public $layout = '@app/modules/h5/views/layouts/main.php';

    public function actionIndex($id)
    {
        $baseModel = new WebPage();
        $model = $baseModel->getArticleDetails($id);        

        return $this->render('index.htm',[
            'model' => $model,
            'GLOBALS' => $this->GLOBALS,
        ]);
    }    

}
