<?php

namespace backend\controllers;

use Yii;
use common\models\Article;
use common\models\ArticleSearch;
use common\models\ProductDetails;
use common\models\ProductDetailsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Skin;
use common\functions\Tools;
use common\models\CommonTagitem;
use common\models\CommonTag;
use backend\models\CommonFun;
use common\models\Comment;
use common\models\ArticleKeywords;

/**
 * ArticleController implements the CRUD actions for Article model.
 */
class ArticleController extends Controller
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
                    // 'imagePathFormat' => "/image/{yyyy}{mm}{dd}/{time}{rand:6}",
                    //'imagePathFormat' => "../../frontend/web/uploads/article/{yyyy}{mm}{dd}/{time}{rand:6}",//上传图片的路径
                    'imagePathFormat' => $path."article/{yyyy}{mm}{dd}/{time}{rand:6}",
                    // 'imagePathFormat' => "/uploads/group/{time}{rand:6}",
                ]
            ],
            'upload' => [
                'class' => 'kucha\ueditor\UEditorAction',
                'config' => [
                    "imageUrlPrefix"  => trim(Yii::$app->params['uploadsUrl'],"/"),//图片访问路径前缀
                    //"imagePathFormat" => "../../frontend/web/uploads/article/{yyyy}{mm}{dd}/{time}{rand:6}", //上传保存路径
                    'imagePathFormat' => $path."article/{yyyy}{mm}{dd}/{time}{rand:6}",
                    //"imageRoot" => Yii::getAlias("@webroot"),
                ],
            ]
        ];
    }

    /**
     * Lists all Article models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ArticleSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Article model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $model->content = Tools::userTextDecode($model->content);
        
        //标签
        $tagIdArr = CommonTagitem::find()->select('tagid')->where(['and',"idtype = 2","itemid = $id"])->orderBy('tagid asc')->asArray()->column();
        $tagNameArr = CommonFun::getConnectArr($tagIdArr,new CommonTag(), 'tagid', 'tagname');

        return $this->render('view', [
            'model' => $model,
            'tagNameArr' => $tagNameArr
        ]);
    }

    /**
     * Creates a new Article model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Article();
        
        //产品列表
        $searchModel = new ProductDetailsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        //肤质列表
        $skinArr = Skin::find()->asArray()->all();
        $skinList = [];
        foreach ($skinArr as $key=>$val){
            $skinList[$val['id']] = $val['skin'].'('.$val['explain'].')';
        }
        
        $post = Yii::$app->request->post();
        if ($post) {
            //表情处理
            $post['Article']['content'] = Tools::userTextEncode($post['Article']['content']);
            
            //添加文章内链
            $tools = new Tools();
            $post['Article']['content'] = $tools->keylink($post['Article']['content']);

            //一级分类
            if(!empty($post['Article']['cate_ids']) && empty($post['Article']['cate_id'])){
                $post['Article']['cate_id'] = $post['Article']['cate_ids'];
            }

            //保存product_id
            preg_match_all('/<div class="data" style="display:none">(\d+)<\/div>/',$post['Article']['content'],$match);
            $post['Article']['product_id'] = join(',',$match[1]);
            preg_replace('/<div class="data" style="display:none">(\d+)<\/div>/','', $post['Article']['content']);
            if($pid_str = $post['Article']['product_id']){
                //保存对应品牌id
                $pbid_arr = ProductDetails::find()->select('brand_id')->where("id in ($pid_str)")->asArray()->column();
                $post['Article']['brand_id'] = join(',',array_flip(array_flip($pbid_arr)));
                //保存对应分类id
                $pcid_arr = ProductDetails::find()->select('cate_id')->where("id in ($pid_str)")->asArray()->column();
                $post['Article']['product_cate_id'] = join(',',array_flip(array_flip($pcid_arr)));
            }
            
            //保存创建人
            $post['Article']['admin_id'] = yii::$app->user->id;
            
            //推荐时间
            if ($model->is_recommend == '1') {
                $model->retime = time();
            };
          
        }

        if ($model->load($post) && $model->save()) {
            //关联文章热词
            //有新标签
            if (!empty($post['Article']['new_tag'])) {
                $new_tag = explode(',', substr($post['Article']['new_tag'],0,strlen($post['Article']['new_tag'])-1));
                foreach ($new_tag as $key=>$val) {
                    //再次确认是否为新标签(当多个网页同时添加)
                    $is_new_tag = CommonTag::find()->where("tagname = '$val' AND type = '2'")->asArray()->all();
                    if (empty($is_new_tag)) {
                        //新数据先存标签表再建立关系
                        $tag = new CommonTag();
                        $tag->tagname = $val;
                        $tag->type = 2;
                        if ($tag->save()) {
                            $tagitem = new CommonTagitem();
                            $tagitem->tagid = $tag->tagid;
                            $tagitem->itemid = $model->id;
                            $tagitem->idtype = 2;
                            $tagitem->save();
                        }
                    } else {
                        //旧数据，是否建立过关系
                        $is_exist = CommonTagitem::find()->where(['and',"tagid = {$is_new_tag['0']['tagid']}","itemid = $model->id","idtype = 2"])->all();
                        if (!$is_exist) {
                            $tagitem = new CommonTagitem();
                            $tagitem->tagid = $is_new_tag['0']['tagid'];
                            $tagitem->itemid = $model->id;
                            $tagitem->idtype = 2;
                            //建立关系后添加次数
                            if ($tagitem->save()) {
                                $count = CommonTag::findOne($is_new_tag['0']['tagid']);
                                $count->count = $count->count+1;
                                $count->save();
                            }
                        }
                    }                    
                }
            }
            //旧标签
            if (!empty($post['Article']['tag_name'])) {
                foreach ($post['Article']['tag_name'] as $key=>$val) {
                    //旧数据，是否建立过关系
                    $is_exist = CommonTagitem::find()->where(['and',"tagid = $val","itemid = $model->id","idtype = 2"])->all();
                    if (!$is_exist) {
                        $tagitem = new CommonTagitem();
                        $tagitem->tagid = $val;
                        $tagitem->itemid = $model->id;
                        $tagitem->idtype = 2;
                        //建立关系后添加次数
                        if ($tagitem->save()) {
                            $count = CommonTag::findOne($val);
                            $count->count = $count->count+1;
                            $count->save();
                        }
                    }
                }
            }             
            //原来有后来全部清空
            if (empty($post['Article']['tag_name']) && empty($post['Article']['new_tag'])) {
                $before = CommonTagitem::find()->where("itemid = $model->id AND idtype = '2'")->all();
                if (!empty($before)) {
                    foreach ($before as $key=>$val) {
                        //全部次数-1
                        $tag = CommonTag::findOne($val->tagid);
                        if (!empty($tag)) {
                            $tag->count = $tag->count > 0 ? $tag->count-1 : $tag->count;$tag->delete();
                            $tag->save();
                        }
                    }
                }
                //清空标签关系
                CommonTagitem::deleteAll(['idtype' => 2,'itemid' => $model->id]);
            }
            //更新标签数据
            CommonFun::updateCommonTag();
            
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'skinList' => $skinList,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * Updates an existing Article model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        //标签
        $tagIdArr = CommonTagitem::find()->select('tagid')->where(['and',"idtype = 2","itemid = $id"])->orderBy('tagid asc')->asArray()->column();

        //产品列表
        $searchModel = new ProductDetailsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        //肤质列表
        $skinArr = Skin::find()->asArray()->all();
        $skinList = [];
        foreach ($skinArr as $key=>$val){
            $skinList[$val['id']] = $val['skin'].'('.$val['explain'].')';
        }      

        $post = Yii::$app->request->post();
        if ($post) {
            //表情处理
            $post['Article']['content'] = Tools::userTextEncode($post['Article']['content']);
            
            //添加文章内链
            $tools = new Tools();
            $post['Article']['content'] = $tools->keylink($post['Article']['content']);
            
            //推荐时间
            if ($post['Article']['is_recommend'] == '1') {
                if ($model->is_recommend == '0') $post['Article']['retime'] = time();
            } else {
                $post['Article']['retime'] = '0';
            }

            //一级分类
            if(!empty($post['Article']['cate_ids']) && empty($post['Article']['cate_id'])){
                $post['Article']['cate_id'] = $post['Article']['cate_ids'];
            }

            //保存product_id
            preg_match_all('/<div class="data" style="display:none">(\d+)<\/div>/',$post['Article']['content'],$match);
            $post['Article']['product_id'] = join(',',$match[1]);
            preg_replace('/<div class="data" style="display:none">(\d+)<\/div>/','', $post['Article']['content']);
            if($pid_str = $post['Article']['product_id']){
                //保存对应品牌id
                $pbid_arr = ProductDetails::find()->select('brand_id')->where("id in ($pid_str)")->asArray()->column();
                $post['Article']['brand_id'] = join(',',array_flip(array_flip($pbid_arr)));
                //保存对应分类id
                $pcid_arr = ProductDetails::find()->select('cate_id')->where("id in ($pid_str)")->asArray()->column();
                $post['Article']['product_cate_id'] = join(',',array_flip(array_flip($pcid_arr)));
            }
           
            //关联文章热词   
            //有新标签
            if (!empty($post['Article']['new_tag'])) {
                $new_tag = explode(',', substr($post['Article']['new_tag'],0,strlen($post['Article']['new_tag'])-1));
                foreach ($new_tag as $key=>$val) {
                    //再次确认是否为新标签(当多个网页同时添加)
                    $is_new_tag = CommonTag::find()->where("tagname = '$val' AND type = '2'")->asArray()->all();
                    if (empty($is_new_tag)) {
                        //新数据先存标签表再建立关系
                        $tag = new CommonTag();
                        $tag->tagname = $val;
                        $tag->type = 2;
                        if ($tag->save()) {
                            $tagitem = new CommonTagitem();
                            $tagitem->tagid = $tag->tagid;
                            $tagitem->itemid = $model->id;
                            $tagitem->idtype = 2;
                            $tagitem->save();
                        }
                    } else {
                        //旧数据，是否建立过关系
                        $is_exist = CommonTagitem::find()->where(['and',"tagid = {$is_new_tag['0']['tagid']}","itemid = $model->id","idtype = 2"])->all();
                        if (!$is_exist) {
                            $tagitem = new CommonTagitem();
                            $tagitem->tagid = $is_new_tag['0']['tagid'];
                            $tagitem->itemid = $model->id;
                            $tagitem->idtype = 2;
                            //建立关系后添加次数
                            if ($tagitem->save()) {
                                $count = CommonTag::findOne($is_new_tag['0']['tagid']);
                                $count->count = $count->count+1;
                                $count->save();
                            }
                        }
                    }
                }
            }
            //旧标签
            if (!empty($post['Article']['tag_name'])) {
                foreach ($post['Article']['tag_name'] as $key=>$val) {
                    //存现存的数据
                    $nowexist[] = $val;
                
                    //旧数据，是否建立过关系
                    $is_exist = CommonTagitem::find()->where(['and',"tagid = $val","itemid = $model->id","idtype = 2"])->all();
                    if (!$is_exist) {
                        $tagitem = new CommonTagitem();
                        $tagitem->tagid = $val;
                        $tagitem->itemid = $model->id;
                        $tagitem->idtype = 2;
                        //建立关系后添加次数
                        if ($tagitem->save()) {
                            $count = CommonTag::findOne($val);
                            $count->count = $count->count+1;
                            $count->save();
                        }
                    }
                }
                
                //如果有删减过已建立关系的热词，则删除已建立的关系
                $delTagitem = array_diff($tagIdArr,$nowexist);                
                if (!empty($delTagitem)) {
                    foreach ($delTagitem as $key=>$val) {
                        //>0则减掉次数
                        $tag = CommonTag::findOne($val);
                        if (!empty($tag)) {
                            $tag->count = $tag->count > 0 ? $tag->count-1 : $tag->count;
                            $tag->save();
                        }
                        CommonTagitem::deleteAll(['tagid' => $val, 'idtype' => '2', 'itemid' => $id]);
                    }
                }
            }            
            //原来有后来全部清空
            if (empty($post['Article']['tag_name']) && empty($post['Article']['new_tag'])) {
                $before = CommonTagitem::find()->where("itemid = $id AND idtype = '2'")->all();
                if (!empty($before)) {
                    foreach ($before as $key=>$val) {
                        //全部次数-1
                        $tag = CommonTag::findOne($val->tagid);
                        if (!empty($tag)) {
                            $tag->count = $tag->count > 0 ? $tag->count-1 : $tag->count;$tag->delete();
                            $tag->save();
                        }
                    }
                }
                //清空标签关系
                CommonTagitem::deleteAll(['idtype' => 2,'itemid' => $id]);
            }    
            //更新标签数据
            CommonFun::updateCommonTag();
        }
        
        if ($model->load($post) && $model->save()) {
            //上下架对应处理文章评论状态
//             Comment::updateAll(['status'=> $model->status],"post_id = $id AND type = 2");
            
            return $this->redirect(['view', 'id' => $model->id]);
        } else {

            $model->content = Tools::userTextDecode($model->content);
            return $this->render('update', [
                'model' => $model,
                'tagIdArr' => $tagIdArr,
                'skinList' => $skinList,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    //改变状态
    public function actionChangeStatus()
    {
        $id = Yii::$app->request->post("id");
        $status = Yii::$app->request->post("status");
        $type = Yii::$app->request->post("type");

        $data['status'] = "0";

        if($model = Article::findOne($id)){
            $status = $status == 1 ? 0 : 1;
            
            //推荐时间
            if($type == "is_recommend"){
                $model->retime = $status == 1 ? time() : 0;
            }
            
            $model->$type = $status;
            $model->save(false);
            
            //处理产品上下架对应评论
//             if($type == "status"){
//                 Comment::updateAll(['status'=> $status],"post_id = $id AND type = 2");
//             }

            $data['status'] = "1";
        }

        echo json_encode($data);
    }

    //产品样式返回
    public function actionSelectProduct(){

        $list = Yii::$app->request->post("id");

        $data = "";
        if($list){
            foreach($list as $val){
                $model = ProductDetails::find()->where("id=".$val)->asArray()->one();
                //数据处理
                $model['price'] = empty($model['price']) ? '暂无报价' : '¥'.$model['price'];
                $model_form = empty($model['form']) ? '' : "/".$model['form']; 

                $data .= '<div class="border"><div class="xgsp" data-id="'.$model['id'].'">';
                $data .= "<a href='/product/details?id=".$model['id']."' target='_self'><img src='".Yii::$app->params['uploadsUrl'] . $model['product_img']."' alt=''/>";
                $data .= '<div class="link-m"><h6 class="ell">'.$model['product_name'].'<div class="data" style="display:none">'.$model['id'].'</div></h6><div class="price">参考价：'.$model['price'].$model_form.'</div>';
                $data .= '<div class="star">';
                for($i=0;$i<$model['star'];$i++){
                    $data .= '<i class="icon ico-star"></i>';
                }
                $data .= '</div></div><i class="icon ico-r"></i></a></div></div><br>';
            }
        }

        echo json_encode($data);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        //删除与关键词的联系
        $tagitemArr = CommonTagitem::find()->where("itemid = $id AND idtype = 2")->asArray()->all();

        foreach ($tagitemArr as $key=>$val) {
            //>0则减掉次数
            $tag = CommonTag::findOne($val['tagid']);
            if (!empty($tag)) {
                $tag->count = $tag->count > 0 ? $tag->count-1 : $tag->count;
                $tag->save();
                //是否与其他文章建立关系，无则删除
                $tag->count == '0' ? $tag->delete() : false;
            }
        }
        CommonTagitem::deleteAll(['idtype' => 2,'itemid' => $id]);
        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
    }
    
    //取消置顶
    public function actionNoStick($id)
    {
        $model = $this->findModel($id);
        $model->stick = 0;
        $model->save(false);

        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
    }

    protected function findModel($id)
    {
        if (($model = Article::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    //更新有插入产品文章的产品分类id字段（正式站用完删）
    public function actionUpdateCate()
    {
        $count = Article::find()->where("product_id != ''")->count();
        $pageSize = 10;
        $pageMin = 0;
        $total = intval(ceil($count/$pageSize));
        
        for($i=1;$i<=$total;$i++) {
        
            $sql = "SELECT id,product_id FROM {{%article}} WHERE product_id !='' limit $pageMin,$pageSize";
            $idArr = Yii::$app->db->createCommand($sql)->queryAll();
        
            if (!empty($idArr)) {
                foreach ($idArr as $key=>$val) {
                    //保存对应分类id
                    $model = Article::findOne($val['id']);
                    $pid_str = $val['product_id'];
                    $pcid_arr = ProductDetails::find()->select('cate_id')->where("id in ($pid_str)")->asArray()->column();                    
                    $model->product_cate_id = join(',',array_flip(array_flip($pcid_arr)));
                    $model->save(false);
                }
                $pageMin = $pageMin + 10;
                usleep(100);
            }
        }
    }
}
