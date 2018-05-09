<?php

namespace backend\controllers;

use Yii;
use common\models\Ranking;
use common\models\RankingSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\functions\Functions;
use yii\sphinx\Query;
use common\models\RankingList;
use yii\sphinx\MatchExpression;
use yii\base\Object;
use common\models\UserSkin;

/**
 * BetaController implements the CRUD actions for Beta model.
 */
class BetaController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    

    /**
     * Lists all Ranking models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index', [

        ]);
    }
    
    public function actionResetSkinTest($id)
    {
        $userSkin = UserSkin::findOne($id);
        if ($userSkin) {
            $userSkin->delete();
            Yii::$app->getSession()->setFlash('success', '操作成功');
        } else {
            Yii::$app->getSession()->setFlash('error', '该用户无测试数据');
        }        
        
        return $this->render('index', [

        ]);
    }
}
