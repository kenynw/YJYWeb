<?php

namespace backend\controllers;

use Yii;
use common\models\HuodongSpecialConfig;
use common\models\HuodongSpecialConfigSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\HuodongDrawLog;
use common\models\HuodongAddress;
use common\models\HuodongSpecialDraw;
use common\models\User;
use common\models\Comment;
use yii\base\Object;

/**
 * HuodongSpecialConfigController implements the CRUD actions for HuodongSpecialConfig model.
 */
class HuodongSpecialConfigController extends Controller
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
    
    public function actions()
    {
        $path = Yii::$app->params['isOnline'] ? "uploads/" : "cs/uploads/";
    
        return [
            'uploads'=>[
                'class' => 'common\widgets\file_upload\UploadAction',
                'config' => [
                    'imagePathFormat' => $path."huodong/{yyyy}{mm}{dd}/{time}{rand:6}",
                ]
            ]
        ];
    }
    
    //活动信息
    private function getHuodongInfo($id) {
        $huoDongdInfo = HuodongSpecialConfig::findOne($id);
        if(!$huoDongdInfo){
            throw new NotFoundHttpException('The huodong not exist.');
        }
        return $huoDongdInfo;
    }

    /**
     * Lists all HuodongSpecialConfig models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new HuodongSpecialConfigSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);                

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single HuodongSpecialConfig model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new HuodongSpecialConfig model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new HuodongSpecialConfig();

        $data  = '';
        if ($data = Yii::$app->request->post()) {
            //处理时间
            $data['HuodongSpecialConfig']['starttime'] = strtotime($data['HuodongSpecialConfig']['starttime']);
            $data['HuodongSpecialConfig']['endtime']   = strtotime($data['HuodongSpecialConfig']['endtime']);
        }

        if ($model->load($data) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing HuodongSpecialConfig model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $data  = '';
        if ($data = Yii::$app->request->post()) {
            //处理时间
            $data['HuodongSpecialConfig']['starttime'] = strtotime($data['HuodongSpecialConfig']['starttime']);
            $data['HuodongSpecialConfig']['endtime']   = strtotime($data['HuodongSpecialConfig']['endtime']);
        }

        if ($model->load($data) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing HuodongSpecialConfig model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the HuodongSpecialConfig model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return HuodongSpecialConfig the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HuodongSpecialConfig::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    public function actionValidateForm () {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new HuodongSpecialConfig();
        $model->load(Yii::$app->request->post());
        return \yii\widgets\ActiveForm::validate($model);
    }
    
    //改变状态
    public function actionChangeStatus()
    {
        $id = Yii::$app->request->post("id");
        $status = Yii::$app->request->post("status");
        $type = Yii::$app->request->post("type");
    
        $data['status'] = "0";
    
        if($model = HuodongSpecialConfig::findOne($id)){
            $status = $status == 1 ? 0 : 1;
            $model->$type = $status;
            $model->save(false);
            $data['status'] = "1";
        }
    
        echo json_encode($data);
    }
    
    //清除表数据
    public function actionDeldata($id)
    {
        $url = dirname(yii::$app->basePath)."/m/web/static/huodong/$id/";

        try {
            switch ($id) {
                //活动1
                case '1':
                    $this->batchUnlink("click_num.txt",$url);
                    HuodongSpecialDraw::deleteAll('hdid = :id', [':id' => $id]);
                    HuodongDrawLog::deleteAll('hid = :id', [':id' => $id]);
                    HuodongAddress::deleteAll('hid = :id', [':id' => $id]);
                    break;
                //活动3                   
                case '3':
                    $this->batchUnlink("click_num.txt",$url);
                    HuodongSpecialDraw::deleteAll('hdid = :id', [':id' => $id]);
                    HuodongDrawLog::deleteAll('hid = :id', [':id' => $id]);
                    HuodongAddress::deleteAll('hid = :id', [':id' => $id]);
                    $delDownload1 = "DELETE FROM {{%log_download_click}} WHERE hid = '$id'"; 
                    Yii::$app->db->createCommand($delDownload1)->execute();
                    $delDownload2 = "DELETE FROM {{%log_button_click}} WHERE button_id = '5'";
                    Yii::$app->db->createCommand($delDownload2)->execute();
                    break;                
            }

            Yii::$app->getSession()->setFlash('success', '操作成功');
            return $this->redirect(['index']);
        } catch (Exception $e) {
            throw new NotFoundHttpException("error");
        }
        Yii::$app->getSession()->setFlash('success', '操作成功');
        return $this->redirect(['index']);
    
    }
    
    //活动读取文件
    static function readFile($hdid,$file) {
        $url = dirname(yii::$app->basePath)."/m/web/static/huodong/".$hdid."/".$file;
    
        if (file_exists($url)) {
            $str = file_get_contents($url);
            $arr1 = explode("#",$str);
            array_pop($arr1);
            $arr2 = [];
    
            foreach ($arr1 as $key => $val) {
                $uid = explode("|",$val);
                $arr2[$key] = $uid[0];
            }
    
            if ($file == 'pv.txt'|| $file == 'change.txt') {//不用去重
                return [count($arr2),count(array_unique($arr2))];
            } else {//去重
                return count(array_unique($arr2));
            }
        } else {
            return 0;
        }
    }
    
    //清除统计文件数据
    static function batchUnlink($str,$url)
    {
        $array=explode(';',$str);
        foreach($array as $val)
        {
            @unlink($url.$val);
        }
    }
    
    //百分比
    static function getPercent($rang_sum,$sum,$prec)
    {
        return round(($rang_sum/$sum),$prec)*100;
    }
    
    //其他活动
    public function actionAct($id)
    {
        return $this->render('act', [
            'model' => $this->findModel($id),
        ]);
    }

    //活动1
    public function actionAct1($id)
    {
        //活动参与情况统计
        //活动开始和结束时间
        $huodongConfig = HuodongSpecialConfig::findOne($id);
        if (empty($huodongConfig)) {
            echo '该活动不存在';die;
        } else {
            $huodongStartTime = $huodongConfig->starttime;
            $huodongEndTime = $huodongConfig->endtime;
        }
        //click_num数组
        $url = dirname(yii::$app->basePath)."/m/web/static/huodong/1/click_num.txt";        
        if (file_exists($url)) {
            $str = file_get_contents($url);
            $arr1 = explode("#",$str);
            array_pop($arr1);
            $arr2 = [];

            foreach (array_reverse($arr1) as $key=>$val) {
                $u = explode("|",$val);
                $arr2[$u[0]] = $u[1];
            }
        } else {
//             echo '文件不存在';
        }

        $participateArr = [];
        if (!empty($arr2)) {
            $huodongStartTime2 = $huodongStartTime;
            for ($i=0;$huodongStartTime2<=($huodongEndTime+86399);$i++) {
                //查看活动用户数
                $uidArr = [];
                foreach ($arr2 as $key=>$val) {
                    if (($huodongStartTime2 <= strtotime($val)) && (strtotime($val) <= ($huodongStartTime2+86399))) {
                        $uidArr[] = $key;
                    }
                }
                //判断新老用户
                $participateArr[$i]['click']['new'] = 0;
                $participateArr[$i]['click']['old'] = 0;
                foreach ($uidArr as $key1=>$val1) {
                    $user = User::findOne($val1);
                    if (!empty($user)) {
                        if (($huodongStartTime <= $user->created_at) && ($user->created_at <= $huodongEndTime)) {
                            $participateArr[$i]['click']['new'] += 1;
                        } elseif ((0 < $user->created_at) && ($user->created_at < $huodongStartTime)) {
                            $participateArr[$i]['click']['old'] += 1;
                        } else {
                            $participateArr[$i]['click']['new'] += 0;
                            $participateArr[$i]['click']['old'] += 0;
                        }
                    } else {
                        //                     echo '该用户不存在1';
                    }
                }

                //发起邀请用户数
                $participateArr[$i]['draw']['new'] = 0;
                $participateArr[$i]['draw']['old'] = 0;
                $huodongDraw = HuodongSpecialDraw::find()->where("hdid = $id AND addtime BETWEEN $huodongStartTime2 AND ($huodongStartTime2+86399)")->groupBy('uid')->all();
                if (!empty($huodongDraw)) {
                    foreach ($huodongDraw as $key=>$val) {
                        $huodongDrawuser = User::findOne($val->uid);
                        if (!empty($huodongDrawuser)) {
                            if (($huodongStartTime <= $huodongDrawuser->created_at) && ($huodongDrawuser->created_at <= $huodongEndTime)) {
                                $participateArr[$i]['draw']['new'] += 1;
                            } elseif ((0 < $huodongDrawuser->created_at) && ($huodongDrawuser->created_at < $huodongStartTime)) {
                                $participateArr[$i]['draw']['old'] += 1;
                            } else {
                                $participateArr[$i]['draw']['new'] += 0;
                                $participateArr[$i]['draw']['old'] += 0;
                            }
                        } else {
                            //                         echo '该用户不存在2';
                        }
                    }
                }

                //助攻人数
                $participateArr[$i]['invite']['new'] = 0;
                $participateArr[$i]['invite']['old'] = 0;
                $drawLog = HuodongDrawLog::find()->where("hid = $id AND UNIX_TIMESTAMP(add_time) BETWEEN $huodongStartTime2 AND ($huodongStartTime2+86399)")->all();
                if (!empty($drawLog)) {
                    foreach ($drawLog as $key=>$val) {
                        $drawLoguser = User::findOne($val->user_id);
                        if (!empty($drawLoguser)) {
                            if (($huodongStartTime <= $drawLoguser->created_at) && ($drawLoguser->created_at <= $huodongEndTime)) {
                                $participateArr[$i]['invite']['new'] += 1;
                            } elseif ((0 < $drawLoguser->created_at) && ($drawLoguser->created_at < $huodongStartTime)) {
                                $participateArr[$i]['invite']['old'] += 1;
                            } else {
                                $participateArr[$i]['invite']['new'] += 0;
                                $participateArr[$i]['invite']['old'] += 0;
                            }
                        } else {
                            //                         echo '该用户不存在3';
                        }
                    }
                }
            
                $huodongStartTime2 = $huodongStartTime2 + 86400;
            }
        }
        
        //邀请人数所占比例
        $inviteArr = [];
        $inviteArr['1-5'] = 0;
        $inviteArr['6-10'] = 0;
        $inviteArr['11-15'] = 0;
        $inviteArr['16-20'] = 0;
        $inviteArr['21-25'] = 0;
        $inviteArr['26-30'] = 0;
        $inviteArr['31-35'] = 0;
        $inviteArr['36-40'] = 0;
        $drawLogAll = HuodongDrawLog::find()->select('count(user_id) num')->where("hid = $id")->groupBy('relation_id')->count();
        $drawLogArr = HuodongDrawLog::find()->select('count(user_id) num')->where("hid = $id")->groupBy('relation_id')->asArray()->column();
        if (!empty($drawLogArr)) {
            foreach ($drawLogArr as $key=>$val) {
                switch ($val)
                {
                    case 1<=$val && $val<=5:
                        $inviteArr['1-5'] += 1;
                        break;
                    case 6<=$val && $val<=10:
                        $inviteArr['6-10'] += 1;
                        break;
                    case 11<=$val && $val<=15:
                        $inviteArr['11-15'] += 1;
                        break;
                    case 16<=$val && $val<=20:
                        $inviteArr['16-20'] += 1;
                        break;
                    case 21<=$val && $val<=25:
                        $inviteArr['21-25'] += 1;
                        break;
                    case 26<=$val && $val<=30:
                        $inviteArr['26-30'] += 1;
                        break;
                    case 31<=$val && $val<=35:
                        $inviteArr['31-35'] += 1;
                        break;
                    case 36<=$val && $val<=40:
                        $inviteArr['36-40'] += 1;
                        break;
                }
            } 
            
            foreach ($inviteArr as $key=>$val) {
                $inviteArr[$key] = $this->getPercent($val, $drawLogAll, 4);
            }
        } 

        return $this->render('act1', [
            'model' => $this->findModel($id),
            'participateArr' => $participateArr,
            'inviteArr' => $inviteArr
        ]);
    }
    
    //活动2
    public function actionAct2($id)
    {
        //活动参与情况统计
        $huodongConfig = HuodongSpecialConfig::findOne($id);
        if (empty($huodongConfig)) {
            echo '该活动不存在';die;
        } else {
            $huodongStartTime = $huodongConfig->starttime;
            $huodongEndTime = $huodongConfig->endtime - 1;
            
            $articleId = $huodongConfig->relation;
            $type = $huodongConfig->type;
        }
    
        //参与情况
        $participateArr = [];
        //下载情况
        $downloadArr = [];
        $huodongStartTime2 = $huodongStartTime;
        
        
        //app文章阅读人数
        $appArticle = (new \yii\db\Query())
            ->from('{{%huodong_statistics_click}}')
            ->where("hid = $id AND type = $type AND relation_id = $articleId AND UNIX_TIMESTAMP(created_at) BETWEEN $huodongStartTime AND $huodongEndTime")
            ->groupBy('user_id')
            ->all();
        
    
        //活动时期
        for ($i=0;$huodongStartTime2 < $huodongEndTime;$i++) {
    
            //app文章阅读人数
            $participateArr[$i]['articleNum']['new'] = 0;
            $participateArr[$i]['articleNum']['old'] = 0;
            if (!empty($appArticle)) {
                foreach ($appArticle as $key=>$val) {
                    //当天
                    if (($huodongStartTime2 <= strtotime($val['created_at'])) && (strtotime($val['created_at']) <= ($huodongStartTime2+86399))) {
                        //判断新老用户
                        $huodongDrawuser = User::findOne($val['user_id']);
                        if (!empty($huodongDrawuser)) {
                            //2017年8月2号作为划分新旧用户的界限
                            if ((1501603200 <= $huodongDrawuser->created_at) && ($huodongDrawuser->created_at <= $huodongEndTime)) {
                                $participateArr[$i]['articleNum']['new'] += 1;
                            } elseif ((0 < $huodongDrawuser->created_at) && ($huodongDrawuser->created_at < 1501603200)) {
                                $participateArr[$i]['articleNum']['old'] += 1;
                            } else {
                                $participateArr[$i]['articleNum']['new'] += 0;
                                $participateArr[$i]['articleNum']['old'] += 0;
                            }
                        } else {
                            //                         echo '该用户不存在2';
                        }
                    }
                }
            }
            
    
            //已登录分享次数
            $participateArr[$i]['shareNum']['old']['ios'] = 0;
            $participateArr[$i]['shareNum']['old']['android'] = 0;
            $participateArr[$i]['shareNum']['new']['ios'] = 0;
            $participateArr[$i]['shareNum']['new']['android'] = 0;
            $participateArr[$i]['shareNum']['guest'] = 0;
            
            $share = (new \yii\db\Query())
            ->from('{{%log_share}}')
            ->where("type = $type AND relation_id = $articleId AND user_id <> '0' AND created_at BETWEEN $huodongStartTime2 AND ($huodongStartTime2+86399)")
            ->all();

            if (!empty($share)) {
                foreach ($share as $key=>$val) {
                    //判断新老用户
                    $huodongDrawuser = User::findOne($val['user_id']);
                    if (!empty($huodongDrawuser)) {
                        if ($val['referer'] == 'ios') {
                            //2017年8月2号作为划分新旧用户的界限
                            if ((1501603200 <= $huodongDrawuser->created_at) && ($huodongDrawuser->created_at <= $huodongEndTime)) {
                                $participateArr[$i]['shareNum']['new']['ios'] += 1;
                            } elseif ((0 < $huodongDrawuser->created_at) && ($huodongDrawuser->created_at < 1501603200)) {
                                $participateArr[$i]['shareNum']['old']['ios'] += 1;
                            } else {
                                $participateArr[$i]['shareNum']['new']['ios'] += 0;
                                $participateArr[$i]['shareNum']['old']['ios'] += 0;
                            }
                        } else {
                            //2017年8月2号作为划分新旧用户的界限
                            if ((1501603200 <= $huodongDrawuser->created_at) && ($huodongDrawuser->created_at <= $huodongEndTime)) {
                                $participateArr[$i]['shareNum']['new']['android'] += 1;
                            } elseif ((0 < $huodongDrawuser->created_at) && ($huodongDrawuser->created_at < 1501603200)) {
                                $participateArr[$i]['shareNum']['old']['android'] += 1;
                            } else {
                                $participateArr[$i]['shareNum']['new']['android'] += 0;
                                $participateArr[$i]['shareNum']['old']['android'] += 0;
                            }
                        }
                    } else {
                        //                         echo '该用户不存在2';
                    }
                }
            }
            
            
            //未登录分享次数
            $share2 = (new \yii\db\Query())
                ->from('{{%log_share}}')
                ->where("type = $type AND relation_id = $articleId AND user_id = '0' AND created_at BETWEEN $huodongStartTime2 AND ($huodongStartTime2+86399)")
                ->count();

            $participateArr[$i]['shareNum']['guest'] = $share2;
            
            
            //下载按钮点击次数
            $downloadArr[$i]['type1'] = 0;
            $downloadArr[$i]['type2'] = 0;

            $downloadType1 = (new \yii\db\Query())
            ->from('{{%log_download_click}}')
            ->where("hid = $id AND type = 1 AND relation_id = $articleId AND UNIX_TIMESTAMP(created_at) BETWEEN $huodongStartTime2 AND ($huodongStartTime2+86399)")
            ->count();
            
            $downloadType2 = (new \yii\db\Query())
            ->from('{{%log_download_click}}')
            ->where("hid = $id AND type = 2 AND relation_id = $articleId AND UNIX_TIMESTAMP(created_at) BETWEEN $huodongStartTime2 AND ($huodongStartTime2+86399)")
            ->count();
            
            $downloadArr[$i]['type1'] = $downloadType1;
            $downloadArr[$i]['type2'] = $downloadType2;
            
    
            $huodongStartTime2 = $huodongStartTime2 + 86400;
        }

        //符合抽奖条件的用户名单
        $draw = (new \yii\db\Query())
            ->select("u.id id,u.username user,u.rank_points rank,p.add_time time")
            ->from("{{%huodong_statistics_played}} p")
            ->leftJoin("{{%user}} u","p.user_id = u.id")
            ->where("hid = $id")
            ->orderBy('u.rank_points DESC')
            ->all();

        return $this->render('act2', [
            'model' => $this->findModel($id),
            'participateArr' => $participateArr,
            'downloadArr' => $downloadArr,
            'draw' => $draw,
        ]);
    }
    
    //活动3
    public function actionAct3($id)
    {
        //活动参与情况统计
        //活动开始和结束时间
        $huodongConfig = HuodongSpecialConfig::findOne($id);
        if (empty($huodongConfig)) {
            echo '该活动不存在';die;
        } else {
            $huodongStartTime = $huodongConfig->starttime;
            $huodongEndTime = $huodongConfig->endtime;
            
            $huodongStartDay = strtotime(date('Y-m-d',$huodongStartTime));
            $huodongEndDay = strtotime(date('Y-m-d',$huodongEndTime))+86399;
        }
        //click_num数组
        $url = dirname(yii::$app->basePath)."/m/web/static/huodong/3/click_num.txt";        
        if (file_exists($url)) {
            $str = file_get_contents($url);
            $arr1 = explode("#",$str);
            array_pop($arr1);
            $arr2 = [];

            foreach (array_reverse($arr1) as $key=>$val) {
                $u = explode("|",$val);
                $arr2[$u[0]] = $u[1];
            }
        } else {
//             echo '文件不存在';
        }
        
        $participateArr = [];
        $downloadArr = [];
        if (!empty($arr2)) {
            $huodongStartTime2 = $huodongStartDay;
            for ($i=0;$huodongStartTime2<=$huodongEndDay;$i++) {  
                //查看活动用户数
                $uidArr = [];
                foreach ($arr2 as $key=>$val) {
                    if (($huodongStartTime2 <= strtotime($val)) && (strtotime($val) <= ($huodongStartTime2+86399))) {
                        $uidArr[] = $key;
                    }
                }
                //判断新老用户
                $participateArr[$i]['click']['new'] = 0;
                $participateArr[$i]['click']['old'] = 0;
                foreach ($uidArr as $key1=>$val1) {
                    $user = User::findOne($val1);
                    if (!empty($user)) {
                        if (($huodongStartTime <= $user->created_at) && ($user->created_at <= $huodongEndTime)) {
                            $participateArr[$i]['click']['new'] += 1;
                        } elseif ((0 < $user->created_at) && ($user->created_at < $huodongStartTime)) {
                            $participateArr[$i]['click']['old'] += 1;
                        } else {
                            $participateArr[$i]['click']['new'] += 0;
                            $participateArr[$i]['click']['old'] += 0;
                        }
                    } else {
                        //                     echo '该用户不存在1';
                    }
                }

                //发起邀请用户数
                $participateArr[$i]['draw']['new'] = 0;
                $participateArr[$i]['draw']['old'] = 0;
                $huodongDraw = HuodongSpecialDraw::find()->where("hdid = $id AND addtime BETWEEN $huodongStartTime2 AND ($huodongStartTime2+86399)")->groupBy('uid')->all();
                if (!empty($huodongDraw)) {
                    foreach ($huodongDraw as $key=>$val) {
                        $huodongDrawuser = User::findOne($val->uid);
                        if (!empty($huodongDrawuser)) {
                            if (($huodongStartTime <= $huodongDrawuser->created_at) && ($huodongDrawuser->created_at <= $huodongEndTime)) {
                                $participateArr[$i]['draw']['new'] += 1;
                            } elseif ((0 < $huodongDrawuser->created_at) && ($huodongDrawuser->created_at < $huodongStartTime)) {
                                $participateArr[$i]['draw']['old'] += 1;
                            } else {
                                $participateArr[$i]['draw']['new'] += 0;
                                $participateArr[$i]['draw']['old'] += 0;
                            }
                        } else {
                            //                         echo '该用户不存在2';
                        }
                    }
                }

                //被助攻次数
                $participateArr[$i]['invite']['new'] = 0;
                $participateArr[$i]['invite']['old'] = 0;
                $drawLog = HuodongDrawLog::find()->select('id,hid,unionid,relation_id,add_time,count(id) num')->where("hid = $id AND UNIX_TIMESTAMP(add_time) BETWEEN $huodongStartTime2 AND ($huodongStartTime2+86399)")->groupBy('relation_id')->all();
                if (!empty($drawLog)) {
                    foreach ($drawLog as $key=>$val) {
                        if (!empty($val->huodongSpecialDraw->user)) {
                            $drawLoguser = $val->huodongSpecialDraw->user->created_at;
                            if (($huodongStartTime <= $drawLoguser) && ($drawLoguser <= $huodongEndTime)) {
                                $participateArr[$i]['invite']['new'] += $val->num;
                            } elseif ((0 < $drawLoguser) && ($drawLoguser < $huodongStartTime)) {
                                $participateArr[$i]['invite']['old'] += $val->num;
                            } else {
                                $participateArr[$i]['invite']['new'] += 0;
                                $participateArr[$i]['invite']['old'] += 0;
                            }
                        } else {
                            //                         echo '该用户不存在3';
                        }
                    }
                }
                
                //下载按钮点击次数
                $downloadArr[$i]['type1'] = 0;
                $downloadArr[$i]['type2'] = 0;
    
                if ($i == 0) {
                    //第一天
                    $betweenStr = "UNIX_TIMESTAMP(created_at) BETWEEN $huodongStartTime AND ($huodongStartTime2+86399)";
                } elseif ($huodongStartTime2 == strtotime(date('Y-m-d',$huodongEndTime))) {
                    //最后一天
                    $betweenStr = "UNIX_TIMESTAMP(created_at) BETWEEN $huodongStartTime2 AND $huodongEndTime";
                } else {
                    $betweenStr = "UNIX_TIMESTAMP(created_at) BETWEEN $huodongStartTime2 AND ($huodongStartTime2+86399)";
                }
                
                $downloadType1 = (new \yii\db\Query())
                ->from('{{%log_button_click}}')
                ->where("button_id = 5")
                ->andWhere("UNIX_TIMESTAMP(add_time) BETWEEN $huodongStartTime2 AND ($huodongStartTime2+86399)")
                ->one();
                
                $downloadType2 = (new \yii\db\Query())
                ->from('{{%log_download_click}}')
                ->where("hid = $id AND type = 3")
                ->andWhere($betweenStr)
                ->count();

                $downloadArr[$i]['type1'] = empty($downloadType1['click_num']) ? '0' : $downloadType1['click_num'];
                $downloadArr[$i]['type2'] = $downloadType2;
            
                $huodongStartTime2 = $huodongStartTime2 + 86400;
            }
        }
        
        //邀请人数所占比例
        $inviteArr = [];
        $inviteArr['1-5'] = 0;
        $inviteArr['6-10'] = 0;
        $inviteArr['11-15'] = 0;
        $inviteArr['16-20'] = 0;
        $inviteArr['21-25'] = 0;
        $inviteArr['26-30'] = 0;
        $inviteArr['31-35'] = 0;
        $inviteArr['36-40'] = 0;
        $inviteArr['41-50'] = 0;
        $drawLogAll = HuodongDrawLog::find()->select('count(user_id) num')->where("hid = $id")->groupBy('relation_id')->count();
        $drawLogArr = HuodongDrawLog::find()->select('count(user_id) num')->where("hid = $id")->groupBy('relation_id')->asArray()->column();
        if (!empty($drawLogArr)) {
            foreach ($drawLogArr as $key=>$val) {
                switch ($val)
                {
                    case 1<=$val && $val<=5:
                        $inviteArr['1-5'] += 1;
                        break;
                    case 6<=$val && $val<=10:
                        $inviteArr['6-10'] += 1;
                        break;
                    case 11<=$val && $val<=15:
                        $inviteArr['11-15'] += 1;
                        break;
                    case 16<=$val && $val<=20:
                        $inviteArr['16-20'] += 1;
                        break;
                    case 21<=$val && $val<=25:
                        $inviteArr['21-25'] += 1;
                        break;
                    case 26<=$val && $val<=30:
                        $inviteArr['26-30'] += 1;
                        break;
                    case 31<=$val && $val<=35:
                        $inviteArr['31-35'] += 1;
                        break;
                    case 36<=$val && $val<=40:
                        $inviteArr['36-40'] += 1;
                        break;
                    case 41<=$val && $val<=50:
                        $inviteArr['41-50'] += 1;
                        break;
                }
            } 
            foreach ($inviteArr as $key=>$val) {
                $inviteArr[$key] = $this->getPercent($val, $drawLogAll, 4);
            }
        } 

        return $this->render('act3', [
            'model' => $this->findModel($id),
            'participateArr' => $participateArr,
            'downloadArr' => $downloadArr,
            'inviteArr' => $inviteArr
        ]);
    }
}
