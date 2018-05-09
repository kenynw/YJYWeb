<?php

namespace backend\controllers;

use Yii;
use common\models\AppVersion;
use common\models\AppVersionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * AppVersionController implements the CRUD actions for AppVersion model.
 */
class AppVersionController extends Controller
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
     * Lists all AppVersion models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AppVersionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AppVersion model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new AppVersion model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AppVersion();

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            $file = UploadedFile::getInstance($model, 'downloadUrl');

            $path = Yii::$app->basePath . "/../frontend/web/";
            if ($file) {
                $filename = "package/yjy_" . time() . rand(1,1000) .  '.' . $file->getExtension();
                $path = $path . $filename;
                $file->saveAs($path);
                $data['AppVersion']['downloadUrl'] = $filename;
            }

            if ($model->load($data) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing AppVersion model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            $file = UploadedFile::getInstance($model, 'downloadUrl');

            $path = Yii::$app->basePath . "/../frontend/web/";
            if ($file) {
                $filename = "package/yjy_" . time() . rand(1,1000) .  '.' . $file->getExtension();
                $path = $path . $filename;
                $file->saveAs($path);
                $data['AppVersion']['downloadUrl'] = $filename;
            }

            if ($model->load($data) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing AppVersion model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
    }

    //改变状态
    public function actionChangeStatus()
    {
        $id = Yii::$app->request->post("id");
        $status = Yii::$app->request->post("status");
        $type = Yii::$app->request->post("type");

        $data['status'] = "0";

        if($model = AppVersion::findOne($id)){
            $status = $status == 1 ? 0 : 1;
            $model->$type = $status;
            $model->save(false);

            $data['status'] = "1";
        }

        echo json_encode($data);
    }

    protected function findModel($id)
    {
        if (($model = AppVersion::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
