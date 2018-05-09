<?php

namespace backend\controllers;

use Yii;
use common\models\ProductDetails;
use common\models\ProductDetailsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use common\models\ProductComponent;
use common\models\ProductRelate;
use backend\models\CommonFun;
use yii\base\Object;
use common\models\CommonTagitem;
use common\models\CommonTag;
use common\models\Brand;
use common\models\BrandProductDetailsSearch;
use common\models\Comment;
use common\models\ProductLink;
use common\functions\Functions;
use common\components\OssUpload;
use QL\QueryList;
use common\models\SkinRecommendProduct;
use common\models\ProductCategory;
use common\functions\Tools;

/**
 * ProductDetailsController implements the CRUD actions for ProductDetails model.
 */
class ProductDetailsController extends Controller
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
                    //'imagePathFormat' => "../../frontend/web/uploads/product_img/{yyyy}{mm}{dd}/{time}{rand:6}",//上传图片的路径
                    'imagePathFormat' => $path."product_img/{yyyy}{mm}{dd}/{time}{rand:6}",
                    // 'imagePathFormat' => "/uploads/banner/{time}{rand:6}",
                ]
            ]
        ];
    }

    /**
     * Lists all ProductDetails models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductDetailsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);  
        
        //品牌添加产品弹窗
        $searchModel2 = new BrandProductDetailsSearch();
        $dataProvider2 = $searchModel2->search(Yii::$app->request->queryParams);
        
        //分类
        $cateList = CommonFun::getCateList($where = 'parent_id <> 0');

        return $this->render('index', [
            'cateList' => $cateList,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchModel2' => $searchModel2,
            'dataProvider2' => $dataProvider2,
        ]);
    }

    //文章添加,编辑页
    public function actionArticleIndex()
    {
        $searchModel = new ProductDetailsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->renderAjax('article-index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Displays a single ProductDetails model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        //成分
        $cateIdArr = ProductRelate::find()->select('component_id')->where("product_id = $id")->orderBy('id asc')->asArray()->column();
        $cateNameArr = CommonFun::getConnectArr($cateIdArr,new ProductComponent(), 'id', 'name');  
        
        //标签
        $tagIdArr = CommonTagitem::find()->select('tagid')->where(['and',"idtype = 1","itemid = $id"])->orderBy('tagid asc')->asArray()->column();
        $tagNameArr = CommonFun::getConnectArr($tagIdArr,new CommonTag(), 'tagid', 'tagname');
        
        //标签2
        $tagIdArr2 = CommonTagitem::find()->select('tagid')->where(['and',"idtype = 3","itemid = $id"])->orderBy('tagid asc')->asArray()->column();
        $tagNameArr2 = CommonFun::getConnectArr($tagIdArr2,new CommonTag(), 'tagid', 'tagname');
        
        //渠道
        for ($i=1;$i<=3;$i++) {
            $productLink = ProductLink::find()->where("product_id = $id AND type = $i")->one();
            if (!empty($productLink) && !empty($productLink->url)) {
                $link[$i]['link'] = $productLink->url;
                $link[$i]['tb_goods_id'] = $productLink->tb_goods_id;
            } else {
                $link[$i]['link'] = '无';
                $link[$i]['tb_goods_id'] = '无';
            }
        }
        
        //是否有图
        $model = $this->findModel($id);
        $path = Yii::$app->params['uploadsUrl'].$model->product_img;
        if(@file_get_contents($path)) { 
            if ($model->has_img == '0') {
                $model->has_img = '1';
                $model->save();
            }
        } else{ 
            if ($model->has_img == '1') {
                $model->has_img = '0';
                $model->save();
            }
        }
        
        return $this->render('view', [
            'model' => $model,
            'cateNameArr' => $cateNameArr,
            'tagNameArr' => $tagNameArr,
            'tagNameArr2' => $tagNameArr2,
            'link' => $link
        ]);
    }

    /**
     * Creates a new ProductDetails model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {   
        //品牌添加产品弹窗
        $searchModel2 = new BrandProductDetailsSearch();
        $dataProvider2 = $searchModel2->search(Yii::$app->request->queryParams);
        $model = new ProductDetails();
        
        //产品保存前处理数据
        $post = Yii::$app->request->post();
        if ($post) {
            //规格单位
            if (!empty($post['ProductDetails']['form'])) {
                switch ($post['ProductDetails']['unit'])
                {
                    case '0':
                        $unit = 'ml';
                        break;
                    case '1':
                        $unit = 'g';
                        break;
                    case '2':
                        $unit = '片';
                        break;
                }               
                $post['ProductDetails']['form'] = trim($post['ProductDetails']['form']).$unit;
            } else {
                $post['ProductDetails']['form'] = '0';
            }
            unset($post['ProductDetails']['unit']);
            
            //批准日期
            if (empty($post['ProductDetails']['product_date'])) {
                $post['ProductDetails']['product_date'] = '0';
            } else {
                $post['ProductDetails']['product_date'] = strtotime($post['ProductDetails']['product_date']);
            }
            
            //是否有图
            if (empty($post['ProductDetails']['product_img'])) {
                $post['ProductDetails']['has_img'] = 0;
            } else {
                //是否编辑过图
                $post['ProductDetails']['edit_img'] = 1;
            }
            //是否有价格
            if ($post['ProductDetails']['price'] > 0) {
                $post['ProductDetails']['has_price'] = 1;
            }
            //是否有品牌
            if ($post['ProductDetails']['brand_id'] > 0) {
                $post['ProductDetails']['has_brand'] = 1;
            }
            //是否完整
            if (($post['ProductDetails']['price'] > 0) && $post['ProductDetails']['product_img']) {
                $post['ProductDetails']['is_complete'] = 1;
            }   
            //各品牌上榜不能超10
            if (!empty($post['ProductDetails']['is_top'])) {
                $topCount = ProductDetails::find()->where(['and',"brand_id = {$post['ProductDetails']['brand_id']}","is_top = 1"])->count();
                $post['ProductDetails']['is_top'] = $topCount > 10 ? '0' : $post['ProductDetails']['is_top'];
            }
            
            //推荐时间
            if ($post['ProductDetails']['is_recommend'] == '1') {
                $post['ProductDetails']['recommend_time'] = time();
            }
            
        }

        //产品保存后处理数据
        if ($model->load($post) && $model->save()) {
            //渠道
            for ($i=1;$i<=3;$i++) {
                if(!empty($post['ProductDetails']['link'.$i])) {
                    $link = 'link'.$i;
                    $tb_goods_id = 'tb_goods_id'.$i;
                    $productLink = new ProductLink();
                    $productLink->product_id = $model->id;
                    $productLink->type = $i;
                    $productLink->url = $model->$link;
                    $productLink->tb_goods_id = $model->$tb_goods_id;
                    $productLink->save(false);
                    
                    //添加返利链接操作记录
                    CommonFun::addAdminLogView('添加产品返利链接与id',$model->id);
                }
            }

            //关联成分
            if (!empty($post['ProductDetails']['component_id'])) {
                foreach ($post['ProductDetails']['component_id'] as $key=>$val) {
                    $componentModel = new ProductRelate();
                    $componentModel->product_id = $model->id;
                    $componentModel->component_id = $val;
                    $componentModel->save();
                }
            }
            
            //关联产品标签
            //有新标签
            if (!empty($post['ProductDetails']['new_tag'])) {
                $new_tag = explode(',', substr($post['ProductDetails']['new_tag'],0,strlen($post['ProductDetails']['new_tag'])-1));
                foreach ($new_tag as $key=>$val) {
                    //再次确认是否为新标签(当多个网页同时添加)
                    $is_new_tag = CommonTag::find()->where("tagname = '$val' AND type = '1'")->asArray()->all();
                    if (empty($is_new_tag)) {
                        //新数据先存标签表再建立关系
                        $tag = new CommonTag();
                        $tag->tagname = $val;
                        if ($tag->save()) {
                            $tagitem = new CommonTagitem();
                            $tagitem->tagid = $tag->tagid;
                            $tagitem->itemid = $model->id;
                            $tagitem->save();
                        }
                    } else {
                        //旧数据，是否建立过关系
                        $is_exist = CommonTagitem::find()->where(['and',"tagid = {$is_new_tag['0']['tagid']}","itemid = $model->id","idtype = 1"])->all();
                        if (!$is_exist) {
                            $tagitem = new CommonTagitem();
                            $tagitem->tagid = $is_new_tag['0']['tagid'];
                            $tagitem->itemid = $model->id;
                            //                             $tagitem->idtype = 1;
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
            if (!empty($post['ProductDetails']['tag_name'])) {                                
                foreach ($post['ProductDetails']['tag_name'] as $key=>$val) {
                    //旧数据，是否建立过关系
                    $is_exist = CommonTagitem::find()->where(['and',"tagid = $val","itemid = $model->id","idtype = 1"])->all();
                    if (!$is_exist) {
                        $tagitem = new CommonTagitem();
                        $tagitem->tagid = $val;
                        $tagitem->itemid = $model->id;
                        //                             $tagitem->idtype = 1;
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
            if (empty($post['ProductDetails']['tag_name']) && empty($post['ProductDetails']['new_tag'])) {
                $before = CommonTagitem::find()->where("itemid = $model->id AND idtype = '1'")->all();
                if (!empty($before)) {
                    foreach ($before as $key=>$val) {
                        //全部次数-1
                        $tag = CommonTag::findOne($val->tagid);
                        if (!empty($tag)) {
                            $tag->count = $tag->count > 0 ? $tag->count-1 : $tag->count;
                            $tag->save();
                        }
                    }
                }
                //清空标签关系
                CommonTagitem::deleteAll(['idtype' => 1,'itemid' => $model->id]);
            }
            
            //关联产品标签2
            //有新标签
            if (!empty($post['ProductDetails']['new_tag2'])) {
                $new_tag = explode(',', substr($post['ProductDetails']['new_tag2'],0,strlen($post['ProductDetails']['new_tag2'])-1));
                foreach ($new_tag as $key=>$val) {
                    //再次确认是否为新标签(当多个网页同时添加)
                    $is_new_tag = CommonTag::find()->where("tagname = '$val' AND type = '3'")->asArray()->all();
                    if (empty($is_new_tag)) {
                        //新数据先存标签表再建立关系
                        $tag = new CommonTag();
                        $tag->tagname = $val;
                        $tag->type = 3;
                        if ($tag->save()) {
                            $tagitem = new CommonTagitem();
                            $tagitem->tagid = $tag->tagid;
                            $tagitem->itemid = $model->id;
                            $tagitem->idtype = 3;
                            $tagitem->save();
                        }
                    } else {
                        //旧数据，是否建立过关系
                        $is_exist = CommonTagitem::find()->where(['and',"tagid = {$is_new_tag['0']['tagid']}","itemid = $model->id","idtype = 3"])->all();
                        if (!$is_exist) {
                            $tagitem = new CommonTagitem();
                            $tagitem->tagid = $is_new_tag['0']['tagid'];
                            $tagitem->itemid = $model->id;
                            $tagitem->idtype = 3;
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
            if (!empty($post['ProductDetails']['tag_name2'])) {
                foreach ($post['ProductDetails']['tag_name2'] as $key=>$val) {
                    //旧数据，是否建立过关系
                    $is_exist = CommonTagitem::find()->where(['and',"tagid = $val","itemid = $model->id","idtype = 3"])->all();
                    if (!$is_exist) {
                        $tagitem = new CommonTagitem();
                        $tagitem->tagid = $val;
                        $tagitem->itemid = $model->id;
                        $tagitem->idtype = 3;
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
            if (empty($post['ProductDetails']['tag_name2']) && empty($post['ProductDetails']['new_tag2'])) {
                $before = CommonTagitem::find()->where("itemid = $model->id AND idtype = '3'")->all();
                if (!empty($before)) {
                    foreach ($before as $key=>$val) {
                        //全部次数-1
                        $tag = CommonTag::findOne($val->tagid);
                        if (!empty($tag)) {
                            $tag->count = $tag->count > 0 ? $tag->count-1 : $tag->count;
                            $tag->save();
                        }
                    }
                }
                //清空标签关系
                CommonTagitem::deleteAll(['idtype' => 3,'itemid' => $model->id]);
            }
            
            //更新标签数据
            CommonFun::updateCommonTag();  
            
            //更新上榜顺序
            if (!empty($post['ProductDetails']['is_top'])) {
                CommonFun::updateProductRank($post['ProductDetails']['brand_id']);
            }
            
            //更新完整性字段
            // $updateCompeleteSql = "UPDATE yjy_product_details SET is_complete = 1 WHERE id = '$model->id' AND price > 0 AND product_img != ''";
            // Yii::$app->db->createCommand($updateCompeleteSql)->execute();
            
            //添加图片操作记录
            if (!empty($model->product_img)) {
                CommonFun::addAdminLogView('添加产品图片',$model->id);
            }
            
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            //分类
            $cateList = CommonFun::getCateList($where = 'parent_id <> 0');
            return $this->render('create', [
                'model' => $model,
                'cateList' => $cateList,
                'searchModel2' => $searchModel2,
                'dataProvider2' => $dataProvider2,
            ]);
        }
    }

    /**
     * Updates an existing ProductDetails model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldModel = $this->findModel($id);
        //成分
        $cateIdArr = ProductRelate::find()->select('component_id')->where("product_id = $id")->orderBy('id asc')->asArray()->column();
        
        //标签
        $tagIdArr = CommonTagitem::find()->select('tagid')->where(['and',"idtype = 1","itemid = $id"])->orderBy('tagid asc')->asArray()->column();
        
        //标签2
        $tagIdArr2 = CommonTagitem::find()->select('tagid')->where(['and',"idtype = 3","itemid = $id"])->orderBy('tagid asc')->asArray()->column();
        
        //处理数据
        $post = '';

        if (Yii::$app->request->post()) {
            $post = Yii::$app->request->post(); 
            
            //规格单位
            if (!empty($post['ProductDetails']['form'])) {
                switch ($post['ProductDetails']['unit'])
                {
                    case '0':
                        $unit = 'ml';
                        break;
                    case '1':
                        $unit = 'g';
                        break;
                    case '2':
                        $unit = '片';
                        break;
                }
                $post['ProductDetails']['form'] = trim($post['ProductDetails']['form']).$unit;
                unset($post['ProductDetails']['unit']);
            }            
            
            //各品牌上榜不能超10
            if (!empty($post['ProductDetails']['is_top'])) {
                $topCount = ProductDetails::find()->where(['and',"brand_id = {$post['ProductDetails']['brand_id']}","is_top = 1"])->count();
                $post['ProductDetails']['is_top'] = $topCount > 10 ? '0' : $post['ProductDetails']['is_top'];
            } else {
                $post['ProductDetails']['ranking'] = 0;
            }
            
            //关联成分
            //if (!empty($post['ProductDetails']['component_id'])) {
            //  $post['ProductDetails']['component_id'] = join(",",$post['ProductDetails']['component_id']);
            //}
            // ProductRelate::deleteAll('product_id = :product_id', [':product_id' => $id]);
            if (!empty($post['ProductDetails']['component_id'])) {
                foreach ($post['ProductDetails']['component_id'] as $key=>$val) {
                    //存现存的数据
                    $nowexist[] = $val;
                    
                    $productRelate = ProductRelate::find()->where("product_id = $id AND component_id = $val")->all();
                    if (empty($productRelate)) {
                        $componentModel = new ProductRelate();
                        $componentModel->product_id = $id;
                        $componentModel->component_id = $val;
                        $componentModel->save();
                    }
                }
                
                //如果有删减过已建立关系的标签，则删除已建立的关系
                $delProductrelate = array_diff($cateIdArr,$nowexist);
                if (!empty($delProductrelate)) {
                    foreach ($delProductrelate as $key=>$val) {
                        ProductRelate::deleteAll(['component_id' => $val, 'product_id' => $id]);
                    }
                }
            }
            
            //批准时间
            if (empty($post['ProductDetails']['product_date'])) {
                $post['ProductDetails']['product_date'] = '0';
            } else {
                $post['ProductDetails']['product_date'] = strtotime($post['ProductDetails']['product_date']);
            }
            
            //是否有图
            if (!empty($post['ProductDetails']['product_img'])) {
                $post['ProductDetails']['has_img'] = 1;
            }
            //是否有价格
            if ($post['ProductDetails']['price'] > 0) {
                $post['ProductDetails']['has_price'] = 1;
            }
            //是否完整
            if (($post['ProductDetails']['price'] > 0) && $post['ProductDetails']['product_img']) {
                $post['ProductDetails']['is_complete'] = 1;
            }
            //是否有品牌
            if ($post['ProductDetails']['brand_id'] > 0) {
                $post['ProductDetails']['has_brand'] = 1;
            }
            if ($post['ProductDetails']['status'] != $model->status) {
                //产品下架对应肤质推荐成分产品删除
                SkinRecommendProduct::updateAll(['status'=> $post['ProductDetails']['status']],"product_id = $model->id");
            }
            
            //推荐时间
            if ($post['ProductDetails']['is_recommend'] == '1') {
                if ($model->is_recommend == '0') $post['ProductDetails']['recommend_time'] = time();
            } else {
                $post['ProductDetails']['recommend_time'] = '0';
            }
            
            //品牌修改时更新原品牌上榜顺序
            //$old_brand_id = $model->brand_id;
        }

        if ($model->load($post) && $model->save()) {
            //上下架对应处理产品评论状态
//             Comment::updateAll(['status'=> $model->status],"post_id = $id AND type = 1");
            //渠道
            //添加返利链接操作记录
            $logStr = '';
            for ($i=1;$i<=3;$i++) {
                $link = 'link'.$i;
                $tb_goods_id = 'tb_goods_id'.$i;
                
                if(!empty($post['ProductDetails']['link'.$i])) {
                    $productLink1 = ProductLink::find()->where("product_id = $model->id AND type = $i")->one();
                    $oldProductLink1 = ProductLink::find()->where("product_id = $model->id AND type = $i")->one();
                    
                    if (empty($productLink1)) {
                        $productLink2 = new ProductLink();
                        $productLink2->product_id = $model->id;
                        $productLink2->type = $i;
                        $productLink2->url = $model->$link;
                        $productLink2->tb_goods_id = $model->$tb_goods_id;
                        $productLink2->save(false);
                        
                        //添加返利链接操作记录
                        if ($i == '1') {
                            $logStr .= '添加淘宝产品返利链接与id | ';
                        } elseif ($i == '2') {
                            $logStr .= '添加京东产品返利链接与id | ';
                        } elseif ($i == '3') {
                            $logStr .= '添加亚马逊产品返利链接与id | ';
                        }
                    } else {
                        $productLink1->url = $model->$link;
                        $productLink1->tb_goods_id = $model->$tb_goods_id;
                        $productLink1->save();
                        
                         if ($productLink1->url != $oldProductLink1->url || $productLink1->tb_goods_id != $oldProductLink1->tb_goods_id) {                           
                            
                            //添加返利链接操作记录
                            if ($i == '1') {
                                $logStr .= '编辑淘宝产品返利链接与id | ';
                            } elseif ($i == '2') {
                                $logStr .= '编辑京东产品返利链接与id | ';
                            } elseif ($i == '3') {
                                $logStr .= '编辑亚马逊产品返利链接与id | ';
                            }
                        }
                    }
                } else {
                    $productLink1 = ProductLink::find()->where("product_id = $model->id AND type = $i")->one();
                    $oldProductLink1 = ProductLink::find()->where("product_id = $model->id AND type = $i")->one();
                    
                    if (!empty($productLink1)) {
                        $productLink1->url = $model->$link;
                        $productLink1->tb_goods_id = $model->$tb_goods_id;
                        $productLink1->save();
                        
                        if ($productLink1->url != $oldProductLink1->url || $productLink1->tb_goods_id != $oldProductLink1->tb_goods_id) {
                            //添加返利链接操作记录
                            if ($i == '1') {
                                $logStr .= '编辑淘宝产品返利链接与id | ';
                            } elseif ($i == '2') {
                                $logStr .= '编辑京东产品返利链接与id | ';
                            } elseif ($i == '3') {
                                $logStr .= '编辑亚马逊产品返利链接与id | ';
                            }                            
                        }
                    }
                }
            }
            //添加返利链接操作记录
            if (!empty($post['ProductDetails']['link1']) || !empty($post['ProductDetails']['link2']) || !empty($post['ProductDetails']['link3'])) {
                CommonFun::addAdminLogView($logStr,$model->id);
            }

            //更新上榜顺序
            //CommonFun::updateProductRank($post['ProductDetails']['brand_id']);
            //CommonFun::updateProductRank($old_brand_id);
            
            //更新完整性字段
            // $updateCompeleteSql = "UPDATE yjy_product_details SET is_complete = 1 WHERE id = '$model->id' AND price > 0 AND product_img != ''";
            // Yii::$app->db->createCommand($updateCompeleteSql)->execute();
            
            //添加图片操作记录
            if ($model->product_img != $oldModel->product_img) {
                CommonFun::addAdminLogView('编辑产品图片',$model->id);
                //是否编辑过图
                $model->edit_img = 1;
                $model->save();
            }
            
            //关联产品标签
            //有新标签
            if (!empty($post['ProductDetails']['new_tag'])) {
                $new_tag = explode(',', substr($post['ProductDetails']['new_tag'],0,strlen($post['ProductDetails']['new_tag'])-1));
                foreach ($new_tag as $key=>$val) {
                    //再次确认是否为新标签(当多个网页同时添加)
                    $is_new_tag = CommonTag::find()->where("tagname = '$val' AND type = '1'")->asArray()->all();
                    if (empty($is_new_tag)) {
                        //新数据先存标签表再建立关系
                        $tag = new CommonTag();
                        $tag->tagname = $val;
                        $tag->type = 3;
                        if ($tag->save()) {
                            $tagitem = new CommonTagitem();
                            $tagitem->tagid = $tag->tagid;
                            $tagitem->itemid = $model->id;
                            //                             $tagitem->idtype = 1;
                            $tagitem->save();
                        }
                    } else {
                        //旧数据，是否建立过关系
                        $is_exist = CommonTagitem::find()->where(['and',"tagid = {$is_new_tag['0']['tagid']}","itemid = $model->id","idtype = 1"])->all();
                        if (!$is_exist) {
                            $tagitem = new CommonTagitem();
                            $tagitem->tagid = $is_new_tag['0']['tagid'];
                            $tagitem->itemid = $model->id;
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
            if (!empty($post['ProductDetails']['tag_name'])) {
                foreach ($post['ProductDetails']['tag_name'] as $key=>$val) {
                    //存现存的数据
                    $nowexist[] = $val;
            
                    //旧数据，是否建立过关系
                    $is_exist = CommonTagitem::find()->where(['and',"tagid = $val","itemid = $model->id","idtype = 1"])->all();
                    if (!$is_exist) {
                        $tagitem = new CommonTagitem();
                        $tagitem->tagid = $val;
                        $tagitem->itemid = $model->id;
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
                        CommonTagitem::deleteAll(['tagid' => $val, 'idtype' => '1', 'itemid' => $id]);
                    }
                }
            }
            //原来有后来全部清空
            if (empty($post['ProductDetails']['tag_name']) && empty($post['ProductDetails']['new_tag'])) {
                $before = CommonTagitem::find()->where("itemid = $id AND idtype = '1'")->all();
                if (!empty($before)) {
                    foreach ($before as $key=>$val) {
                        //全部次数-1
                        $tag = CommonTag::findOne($val->tagid);
                        if (!empty($tag)) {
                            $tag->count = $tag->count > 0 ? $tag->count-1 : $tag->count;
                            $tag->save();
                        }
                    }
                }
                //清空标签关系
                CommonTagitem::deleteAll(['idtype' => 1,'itemid' => $id]);
            }
            
            //关联产品标签2
            //有新标签
            if (!empty($post['ProductDetails']['new_tag2'])) {
                $new_tag2 = explode(',', substr($post['ProductDetails']['new_tag2'],0,strlen($post['ProductDetails']['new_tag2'])-1));
                foreach ($new_tag2 as $key=>$val) {
                    //再次确认是否为新标签(当多个网页同时添加)
                    $is_new_tag = CommonTag::find()->where("tagname = '$val' AND type = '3'")->asArray()->all();
                    if (empty($is_new_tag)) {
                        //新数据先存标签表再建立关系
                        $tag = new CommonTag();
                        $tag->tagname = $val;
                        $tag->type = 3;
                        if ($tag->save()) {
                            $tagitem = new CommonTagitem();
                            $tagitem->tagid = $tag->tagid;
                            $tagitem->itemid = $model->id;
                            $tagitem->idtype = 3;
                            $tagitem->save();
                        }
                    } else {
                        //旧数据，是否建立过关系
                        $is_exist = CommonTagitem::find()->where(['and',"tagid = {$is_new_tag['0']['tagid']}","itemid = $model->id","idtype = 3"])->all();
                        if (!$is_exist) {
                            $tagitem = new CommonTagitem();
                            $tagitem->tagid = $is_new_tag['0']['tagid'];
                            $tagitem->itemid = $model->id;
                            $tagitem->idtype = 3;
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
            if (!empty($post['ProductDetails']['tag_name2'])) {
                foreach ($post['ProductDetails']['tag_name2'] as $key=>$val) {
                    //存现存的数据
                    $nowexist[] = $val;
            
                    //旧数据，是否建立过关系
                    $is_exist = CommonTagitem::find()->where(['and',"tagid = $val","itemid = $model->id","idtype = 3"])->all();
                    if (!$is_exist) {
                        $tagitem = new CommonTagitem();
                        $tagitem->tagid = $val;
                        $tagitem->itemid = $model->id;
                        $tagitem->idtype = 3;
                        //建立关系后添加次数
                        if ($tagitem->save()) {
                            $count = CommonTag::findOne($val);
                            $count->count = $count->count+1;
                            $count->save();
                        }
                    }
                }
            
                //如果有删减过已建立关系的热词，则删除已建立的关系
                $delTagitem = array_diff($tagIdArr2,$nowexist);
                if (!empty($delTagitem)) {
                    foreach ($delTagitem as $key=>$val) {
                        //>0则减掉次数
                        $tag = CommonTag::findOne($val);
                        if (!empty($tag)) {
                            $tag->count = $tag->count > 0 ? $tag->count-1 : $tag->count;
                            $tag->save();
                        }
                        CommonTagitem::deleteAll(['tagid' => $val, 'idtype' => '3', 'itemid' => $id]);
                    }
                }
            }
            //原来有后来全部清空
            if (empty($post['ProductDetails']['tag_name2']) && empty($post['ProductDetails']['new_tag2'])) {
                $before = CommonTagitem::find()->where("itemid = $id AND idtype = '3'")->all();
                if (!empty($before)) {
                    foreach ($before as $key=>$val) {
                        //全部次数-1
                        $tag = CommonTag::findOne($val->tagid);
                        if (!empty($tag)) {
                            $tag->count = $tag->count > 0 ? $tag->count-1 : $tag->count;
                            $tag->save();
                        }
                    }
                }
                //清空标签关系
                CommonTagitem::deleteAll(['idtype' => 3,'itemid' => $id]);
            }
            //更新标签数据
            CommonFun::updateCommonTag();
            
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $model->product_date = date('Y-m-d',$model->product_date);
            //渠道
            for ($i=1;$i<=3;$i++) {
                $link = 'link'.$i;
                $tb_goods_id = 'tb_goods_id'.$i;
                $productLink = ProductLink::find()->where("product_id = $id AND type = $i")->one();
                $model->$link = empty($productLink) ? '' : $productLink->url;
                $model->$tb_goods_id = empty($productLink) ? '' : $productLink->tb_goods_id;
            }
            //分类
            $cateList = CommonFun::getCateList($where = 'parent_id <> 0');

            return $this->render('update', [
                'cateIdArr' => $cateIdArr,
                'tagIdArr' => $tagIdArr,
                'tagIdArr2' => $tagIdArr2,
                'cateList' => $cateList,
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing ProductDetails model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id,$url)
    {
        $this->findModel($id)->delete();
        
        //删除成分、热词、渠道链接关系
        ProductRelate::deleteAll('product_id = :product_id', [':product_id' => $id]);
        CommonTagitem::deleteAll(['idtype' => 1,'itemid' => $id]);
        ProductLink::deleteAll('product_id = :product_id', [':product_id' => $id]);

        return $this->redirect([$url]);
    }

    /**
     * Finds the ProductDetails model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductDetails the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductDetails::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    //添加成分
    public function actionSetComponentSort($id) {
        $post = Yii::$app->request->post();
        if ($post) {
            $model = $this->findModel($id);
            $model->component_id = $post['items'];
            if ($model->save()) {
                $data = ['status' => 1, 'msg' =>'修改成功'];
            } else {
                $data = ['status' => 0, 'msg' =>'修改失败'];
            }            
            return json_encode($data);
        }
    }
    //改变状态
    public function actionChangeStatus()
    {
        $id = Yii::$app->request->post("id");
        $status = Yii::$app->request->post("status");
        $type = Yii::$app->request->post("type");
    
        $data['status'] = "0";

        if($model = ProductDetails::findOne($id)){
            $status = $status == 1 ? 0 : 1;
            if ($type == 'is_top') {
                $status = empty($model->brand_id)&&!empty($status) ? 0 : $status;
                $model->ranking = '0'; 
            }         

            //推荐时间
            if($type == "is_recommend"){
                $model->recommend_time = $status == 1 ? time() : 0;
            }            

            $model->$type = $status;
            $model->save(false);
            
            if ($type == 'is_top') {
                if (!empty($status)) {
                    //更新上榜顺序
                    CommonFun::updateProductRank($model->brand_id);
                }
            }
            
            //处理产品上下架对应评论
//             if($type == "status"){
//                 Comment::updateAll(['status'=> $status],"post_id = $id AND type = 1");
//             }
            //产品上下架对应肤质推荐成分产品删除
            if ($type == "status") {
                SkinRecommendProduct::updateAll(['status'=> $status],"product_id = $model->id");
            }
    
            $data['status'] = "1";
        }
    
        echo json_encode($data);
    }
    
    //搜索成分
    public function actionSearchComponent ($q){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!$q) {
            return $out;
        }
        
        $data = ProductComponent::find()
        ->select('id, name as text')
        ->andFilterWhere(['like', 'name', $q]);

        //中英文括号能互搜...
        if (preg_match('/[（）]/',$q)) {        
            $str1 = preg_replace ('/（/', '(', $q);
            $str2 = preg_replace ('/）/', ')', $str1);
            $data = $data->orFilterWhere(['like', 'name', $str2]);
        } elseif (preg_match('/[\(\)]/',$q)) {
            $str1 = preg_replace ('/\(/', '（', $q);
            $str2 = preg_replace ('/\)/', '）', $str1);
            $data = $data->orFilterWhere(['like', 'name', $str2]);
        }
        
        $data = $data->limit(20)
        ->orderBy("CHAR_LENGTH(name)")
        ->asArray()
        ->all();    
        
        $out['results'] = array_values($data);
    
        return $out;
    }
    
    //搜索标签
    public function actionSearchTag ($q,$type = 1){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
//         $out = ['results' => ['tagid' => '', 'text' => '']];
        $out = ['results' => []];
        if (!$q) {
            return $out;
        }
    
        $data = (new \yii\db\Query())
              ->select('tagid as id, tagname as text')
              ->from('{{%common_tag}}')
              ->where(['like', 'tagname', $q])
              ->andWhere("type = $type")
              ->limit(20)
              ->all();    

        $out['results'] = array_values($data);
    
        return $out;
    }
    
    //搜索品牌
    public function actionSearchBrand ($q,$where = ''){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!$q) {
            return $out;
        }
    
        $data = Brand::find()
            ->select("id, CASE WHEN `name` IS null OR name='' THEN ename ELSE `name` END AS text")
            ->andFilterWhere(['like', 'name', $q])
            ->orFilterWhere(['like', 'ename', $q])
            ->orFilterWhere(['like', 'alias', $q])
            ->andWhere($where)
            ->limit(20)
            ->asArray()
            ->all();

        $out['results'] = array_values($data);
    
        return $out;
    }
    
    //品牌产品页
    //移除品牌
    public function actionBrandRemove ($id,$brand_id,$is_top = ''){
        $model= $this->findModel($id);
        //上榜数，总产品数
        if ($model) {
            $model->brand_id = '0';
            if (!empty($is_top)) {
                $model->is_top = '0';
                $model->ranking = '0';
            }
            $model->save();
        }

        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
//         return empty($is_top) ? $this->redirect(['index','ProductDetailsSearch[brand_id]'=> $brand_id,'btype'=>'brand']) : $this->redirect(['index','ProductDetailsSearch[brand_id]'=> $brand_id,'ProductDetailsSearch[is_top]'=> 1,'btype'=>'brand']);
    }
    
    //批量移除品牌
    public function actionBrandRemoveAll (){
        $ids = Yii::$app->request->post('id');
        $brand_id = Yii::$app->request->post('brand_id');
        $is_top = Yii::$app->request->post('is_top');
    
        foreach ($ids as $key=>$val) {
            $model= $this->findModel($val);
            if ($model) {
                $model->brand_id = '0';
                if (!empty($is_top)) {
                    $model->is_top = '0';
                    $model->ranking = '0';
                }
                $model->save();
            }
        }
    
        $url = Yii::$app->request->referrer;
        return $this->redirect($url);
//         return empty($is_top) ? $this->redirect(['index','ProductDetailsSearch[brand_id]'=> $brand_id,'btype'=>'brand']) : $this->redirect(['index','ProductDetailsSearch[brand_id]'=> $brand_id,'ProductDetailsSearch[is_top]'=> 1,'btype'=>'brand']);
    }
    
    //添加品牌
    public function actionBrandAddProduct (){
        $ids = Yii::$app->request->post('id');
        
        foreach ($ids as $key=>$val) {
            $model= $this->findModel($val);
            $model->brand_id = Yii::$app->request->post('brand_id');
            if (empty(Yii::$app->request->post('is_top'))) {
                $model->is_top = '0';
            } else {
                //同个品牌上榜数不超过10
                $count = ProductDetails::find()->select('id')->where("brand_id = $model->brand_id AND is_top = 1")->count();
                if ($count < 10) {
                     $model->is_top = '1';
                }
            }
            $model->status = '1';
            $model->save();
            
            //更新上榜顺序
           //CommonFun::updateProductRank($model->brand_id);
        }
        
        //添加品牌操作记录
        if (empty(Yii::$app->request->post('is_top'))) {
            CommonFun::addAdminLogView('添加产品',$model->brand_id);
        } else {
            CommonFun::addAdminLogView('添加上榜产品',$model->brand_id);
        }
        
        $data['status'] = "1";
        
        echo json_encode($data);

    }
    
    //单项修改分类
    public function actionUpdateCate (){
        $id = Yii::$app->request->post('id');
        $cate_id = Yii::$app->request->post('cate_id');
    
        $model = ProductDetails::findOne($id);
        $model->cate_id = $cate_id;
        if ($model->save()) {
            $data['status'] = "1";
        } else {
            $data['status'] = "0";
        }  
    
        echo json_encode($data);
    }
    
    //底部批量修改
    public function actionBottomUpdate (){
        $ids = Yii::$app->request->post('id');
        $type = Yii::$app->request->post('type');
        $type_id = Yii::$app->request->post('type_id');
    
        foreach ($ids as $key=>$val) {
            $model= $this->findModel($val);
            switch ($type)
            {
                case 'is_recommend':
                    $model->$type = '1';
                    $model->save();
                    break;
                case 'is_top':
                    if (!empty($model->brand_id)) {
                        //同个品牌上榜数不超过10
                        $count = ProductDetails::find()->select('id')->where("brand_id = $model->brand_id AND is_top = 1")->count();
                        if ($count < 10) {
                            $model->$type = '1';
                            $model->save();
                            //更新上榜顺序
                            CommonFun::updateProductRank($model->brand_id);
                        }
                    }
                    break;
                default:
                $model->$type = $type_id;
                $model->save();
            }
        }

        $data['status'] = "1";
    
        echo json_encode($data);
    }

    /**
     * 产品推广链接导入excel
     * @return mixed
     */
    public function actionInsertExcel()
    {
        set_time_limit(0);
        ini_set('memory_limit','256M');
        require Yii::getAlias('@common').'/extensions/PHPExcel/Classes/PHPExcel.php';

        $data = array();
        if($_POST){

            if($_FILES["file"]["error"] > 0){
                Yii::$app->getSession()->setFlash('error', '文件上传失败,请重新上传..');
            }

            $excelFile = '';    //文件名
            $filepath = Yii::$app->basePath."/web/uploads/product_link";
            $arr=explode(".", $_FILES["file"]["name"]);
            $hz=strtolower($arr[count($arr)-1]);

            if(!is_dir($filepath)) { mkdir($filepath, 0777); chmod($filepath, 0777);}
            $randname = date('YmdHi',time()).rand(1000, 9999).".".$hz;
            if(is_uploaded_file($_FILES["file"]["tmp_name"])){      //将临时位置的文件移动到指定的目录上即可
                if(move_uploaded_file($_FILES["file"]["tmp_name"], $filepath.'/'.$randname)){
                    $excelFile = $filepath.'/'.$randname;       //上传成功的节奏
                    chmod($excelFile, 0777);
                }
            }
            if(!$excelFile){        //文件不存在
                //$data = array('error'=>'2','msg'=>'文件上传失败,请重新上传,检查文件名..','info'=>'');
                Yii::$app->getSession()->setFlash('error', '文件上传失败,请重新上传,检查文件名..');
            }else{      //读取Excel

                if(in_array($hz,array('xls','xlsx'))){
                    $phpexcel=new \PHPExcel();

                    if ($hz == "xls") {
                        $excelReader = \PHPExcel_IOFactory::createReader('Excel5');
                    } else {
                        $excelReader = \PHPExcel_IOFactory::createReader('Excel2007');
                    }

                    $phpexcel    = $excelReader->load($excelFile)->getSheet(0);//载入文件并获取第一个sheet
                    $total_line  = $phpexcel->getHighestRow();//总行数
                    $total_column= $phpexcel->getHighestColumn();//总列数

                    $result = [];
                    $total = 0;
                    $success = 0;

                    if($total_line > 1){
                        //同一批同个时间
                        $time = time();
                        for($row = 2;$row <= $total_line; $row++){
                            $list = array();
                            for($column = 'A'; $column <= $total_column; $column++){
                                $list[] = trim($phpexcel->getCell($column.$row)->getValue());
                            }
                            if(empty($list[0]) && empty($list[1]) && empty($list[2])){
                                continue;
                            }
                            //提示没有平台id
                            if(empty($list[0])){
                                $one['product_id'] = 0;
                                $one['tb_id'] = $list[1];
                                $one['jd_id'] = $list[1];
                                $one['tbStatus'] = '无产品Id';
                                $one['jdStatus'] = '无产品Id';
                                $result[] = $one;
                                continue;
                            }

                            if(empty($list[1])){
                                $tbStatus = '无淘宝id';
                            }

                            if(empty($list[2])){
                                $jdStatus = '无京东id';
                            }

                            $check_tb = 0;
                            $check_jd = 0;
                            if($list[0] && $list[1]){//判断有无淘宝推广链接
                                $check_tb = ProductLink::find()->where(['product_id'=>$list[0],'type'=>1,'tb_goods_id'=>$list[1]])->one();
                                if($check_tb){
                                    $tbStatus = '已存在';
                                }
                            }

                            if($list[0] && $list[2]){//判断有无京东推广链接
                                $check_jd = ProductLink::find()->where(['product_id'=>$list[0],'type'=>2,'tb_goods_id'=>$list[2]])->one();
                                if($check_jd){
                                    $jdStatus = '已存在';
                                }
                            }
                            if(empty($check_tb) && $list[1]){
                                //一行行的插入数据库操作
                                $model = new ProductLink;
                                $model->product_id = (integer)($list[0]);
                                $model->update_time = $time;
                                $model->add_time = $time;
                                $model->tb_goods_id = $list[1];
                                $model->type = 1;
                                $model->admin_id = yii::$app->user->id;
                                //$model->save() ? $tb_ok .=  $list[1].',' : $info .= 'tbID:' .$list[1].'插入数据库失败---' ;
                                $tbStatus = $model->save() ?  '成功' : '失败';
                                $success += 1;
                            }

                            if(empty($check_jd) && $list[2]){
                                //$link = parse_url($list[3]);
                                //一行行的插入数据库操作
                                $model = new ProductLink;
                                $model->product_id = (integer)($list[0]);
                                $model->update_time = $time;
                                $model->add_time = $time;
                                $model->tb_goods_id = $list[2];
                                $model->type = 2;
                                $model->admin_id = yii::$app->user->id;
                                //$model->save() ? $jd_ok .= $list[2].',' : $info .='jdID:' .$list[2].'插入数据库失败---' ;
                                $jdStatus = $model->save() ?  '成功' : '失败';
                                
                                if ($tbStatus == '失败') {
                                    $success += 1;
                                }
                                if ($tbStatus != '失败' && $tbStatus != '成功') {
                                    $success += 1;
                                }
                            }
                            
                            $one['tbStatus'] = isset($tbStatus) ? $tbStatus : '失败';
                            $one['jdStatus'] = isset($jdStatus) ? $jdStatus : '失败';
                            $one['jd_id'] = $list[2];
                            $one['tb_id'] = $list[1];
                            $one['product_id'] = $list[0];
                            $result[] = $one;
                            $total += 1;
                        }
                        $result['total'] = $total;
                        $result['success'] = $success;
                    }

                    return $this->render('insert-excel', ['result'=>$result]);
                }
            }
        }else{

            return $this->render('insert-excel');
        }
    }
    
    /**
     * [actionGrap 批量添加]
     * @return [type] [description]
     */
    public function actionGrap(){
        $keyword   =    Yii::$app->request->post('keyword');
        $min       =    Yii::$app->request->post('min','1');
        $max       =    Yii::$app->request->post('max','');
        $min       =    intval($min);
        $max       =    intval($max);
    
        $msg       =    "";
        set_time_limit(300);
    
        if($keyword){
            if(!$max){
                $url        = 'https://api.bevol.cn/search/goods/index?keywords=' . urlencode($keyword) . '&p=1';
                $data       = Functions::http_judu($url,[],'post');
                $arr        = json_decode($data,true);
                $max        = ceil(intval($arr['data']['total']))/20;
            }
            if($min > $max) return false;
            $t1     = microtime(true);
            //产品列表接口,最多十页。
            for ($i = $min; $i <= $max ; $i++) {
                $url        = 'https://api.bevol.cn/search/goods/index?keywords=' . urlencode($keyword) . '&p='.$i;
                $dataArr    = Functions::http_judu($url,[],'post');
                //开始采集
                if($dataArr){
                    $arr = json_decode($dataArr,true);
                    if($arr['data']['items']){
                        $return  = self::addProduct($arr['data']['items'],$keyword);
                        $msg    .= $return['msg'];
                        if(empty($msg)){
                            $msg .= '第'.$i.'页入库失败，请确认搜索词与产品名匹配<br/>';
                        }
                    }else{
                        break;
                    }
                } else {
                    break;
                }
                unset($url);
                unset($dataArr);
                
                if (!empty($return['arr'])) {
                    foreach ($return['arr'] as $key=>$val) {
                        $msgArr['arr'][] = $val;
                    }
                }
            }
            $t2   = microtime(true);
            $msg .= '程序耗时'.round($t2-$t1,3).'秒';

            if (empty($msgArr['arr'])) {
                return $this->render('grap',[
                    'msg'=>$msg,
                ]);
            } else {
                return $this->redirect(['index',
                    'grap' => serialize($msgArr['arr']),
                ]);
            }
             
        } else {
            return $this->render('grap',[
                'msg'=>$msg,
            ]);
        }
    }

    //添加产品
    static function addProduct($item,$keyword){
        $msg    = "";
        $msgArr = [];
        $time   = time();
        $minTime= '1511971200';

        foreach($item as $value){
            //判断产品名是否为空
            if(empty($value['title'])) continue;

            //判断产品是否存在;
            $sql            = "SELECT id,product_img,created_at FROM {{%product_details}} WHERE product_name = '" . addslashes($value['title']) ."'";
            $productInfo    = Yii::$app->db->createCommand($sql)->queryOne();

            $product_id     = $productInfo ? $productInfo['id'] : '';
            $product_img    = $productInfo ? $productInfo['product_img'] : '';
            $createImageTime= $productInfo ? $productInfo['created_at'] : 0;

            $dataJson = Functions::http_judu('https://api.bevol.cn/entity/info2/goods',['mid'=>$value['mid']],'post');
            $newData  = json_decode($dataJson,true);
            $newData  = $newData['ret'] == 0 ? $newData['result'] : '';
            //产品成分处理
            $componentData  = [];
            $component_list = [];
            //上传图片
            $filename    =  $product_img;
            if(!empty($value['image'])){
                if($createImageTime == 0 || $createImageTime >= $minTime){
                    $url      = 'https://img0.bevol.cn/Goods/source/'.$value['image'] . '@90p';
                    $filename =  Functions::uploadUrlimg($url,'product_img');
                    unset($url);
                }
            }

            $productData   = [
                'has_price'       =>  $value['price'] ? "1" : "0",
                'is_complete'     =>  $value['price'] && $filename ? '1' : '0',
                'has_img'         =>  $filename ? "1" : "0",
                'product_img'     =>  $filename,
                'standard_number' =>  $value['approval'],
                'updated_at'      =>  $time
            ];
            if(!$newData || !isset($newData['entityInfo']['composition'])) continue;

            foreach ($newData['entityInfo']['composition'] as $k => $v) {
                //查询是否存在
                $component_id = '';
                $sql            = "SELECT id FROM yjy_product_component WHERE name = '" . addslashes($v['title']) ."'";
                $component_id   = Yii::$app->db->createCommand($sql)->queryScalar();
                if(!$component_id){
                    $componentData  = [
                        'name' => addslashes($v['title']),
                        'ename'=> $v['english'],
                        'cas'  => $v['cas'],
                        'alias'=> $v['otherTitle'],
                        'risk_grade'=> $v['safety'],
                        'is_active' => $v['active']   ? 1 : 0,
                        'is_pox'    => $v['acneRisk'] ? 1 : 0,
                        'component_action' => isset($v['usedTitle']) ? $v['usedTitle'] : '',
                        'description' => isset($v['remark']) ? $v['remark'] : '',
                        'created_at'  => $time,
                    ];
                    $component_id = self::pdo_insert('yjy_product_component',$componentData,$component_id);
                }
                //插入成分列表
                $component_list[] = $component_id;
            }
            //生产国
            $productData["product_country"] = $newData['entityInfo']['goods']['country'];
            $productData["product_company"] = $newData['entityInfo']['goods']['company'];
            $productData["en_product_company"] = $newData['entityInfo']['goods']['companyEnglish'];
            $productData["product_date"] = $newData['entityInfo']['goods']['approvalDate'];

            //存在更新，不存在入库
            if($product_id){
                $product_id = self::pdo_insert('yjy_product_details',$productData,$product_id);
                //先删除关联数据
                $sql = "DELETE FROM {{%product_relate}} WHERE product_id='$product_id'";
                Yii::$app->db->createCommand($sql)->execute();
                //添加成分
                if($component_list){
                    foreach($component_list as $component_id) {
                        if($component_id){
                            $sql = "INSERT INTO {{%product_relate}} (product_id,component_id) VALUES ({$product_id},{$component_id})";
                            Yii::$app->db->createCommand($sql)->execute();
                        }
                    }
                }
                $msg .= "<span style='color:green'>更新产品</span>id：" . $product_id . "<br/>";
                $msgArr['arr'][] = $product_id;
            }else{
                //处理分类
                if(isset($value['category'])){
                    if($value['category'] == 12 || $value['category'] == 15){
                        $value['category'] = 6;
                    }else if($value['category'] == 30 || $value['category'] == 47){
                        $value['category'] = 38;
                    }else if($value['category'] == 20){
                        $value['category'] = 13;
                    }
                }

                $cateList = array(6,7,8,9,10,11,12,13,15,20,47,30,38);
                $productData   = [
                    'id'                =>  $value['id'],
                    'product_name'      =>  addslashes($value['title']),
                    'alias'             =>  addslashes($value['alias']),
                    'remark'            =>  addslashes($value['remark']),
                    'price'             =>  $value['price'],
                    'product_img'       =>  $filename,
                    'form'              =>  isset($value['capacity']) ? $value['capacity'] : "",
                    'cate_id'           =>  in_array($value['category'],$cateList) ? $value['category'] : '53',
                    'standard_number'   =>  $value['approval'],
                    'has_img'           =>  $filename ? "1" : "0",
                    'has_price'         =>  $value['price'] ? "1" : "0",
                    'is_complete'       =>  $value['price'] && $filename ? '1' : '0',
                    'star'              =>  isset($newData['entity']['safety_1_num']) ? $newData['entity']['safety_1_num'] : 0,
                    'created_at'        =>  $time,
                    'is_complete'       =>  $value['price'] && $filename ? '1' : '0',
                ];
                if($newData){
                    //生产国
                    $productData["product_country"] = $newData['entityInfo']['goods']['country'];
                    $productData["product_company"] = $newData['entityInfo']['goods']['company'];
                    $productData["en_product_company"] = $newData['entityInfo']['goods']['companyEnglish'];
                    $productData["product_date"] = $newData['entityInfo']['goods']['approvalDate'];
                }
                //产品成分关系
                $product_id = self::pdo_insert('yjy_product_details',$productData);

                if($component_list){
                    foreach($component_list as $component_id){
                        if($component_id){
                            $sql = "INSERT INTO {{%product_relate}} (product_id,component_id) VALUES ({$product_id},{$component_id})";
                            Yii::$app->db->createCommand($sql)->execute();
                        }
                    }
                    $msg .= "<span style='color:red'>插入产品</span>id：" . $product_id . "<br/>";
                    $msgArr['arr'][] = $product_id;
                }
                unset($filename);
            }
            //添加产品功效
            self::AddEffect($product_id);

            unset($productData);
            unset($newData);
            unset($componentData);
            unset($component_list);
            usleep(100);
        }
        $msgArr['msg'] = $msg;
        return $msgArr;
    }

    //添加/更新操作(返回id)
    static function pdo_insert($tablename, $insertsqlarr,$id=''){

        if($id){
            $update_data = "";
            foreach ($insertsqlarr as $key => $val) {
                $update_data .= $key . "='".$val . "',";
            }
            $update_data = trim($update_data,",");
            
            if ($tablename == 'yjy_product_details') {
                $time = time();
                $update_data .= ",updated_at = $time";
            }
            
            $sql = "UPDATE $tablename SET {$update_data} WHERE id=$id";
            $result = Yii::$app->db->createCommand($sql)->execute();

            return $id;
        }else{
            $insertkeysql = $insertvaluesql = $comma = '';
            foreach ($insertsqlarr as $insert_key => $insert_value) {
                $insertkeysql .= $comma.'`'.$insert_key.'`';
                $insertvaluesql .= $comma.'\''.$insert_value.'\'';
                $comma = ', ';
            }
            $sql = 'INSERT IGNORE INTO '.$tablename.' ('.$insertkeysql.') VALUES ('.$insertvaluesql.')';
            $result = Yii::$app->db->createCommand($sql)->execute();

            return Yii::$app->db->getLastInsertId();
        }

    }

    //添加产品功效
    static function AddEffect($id){

        if(empty($id)) return false;

        $effectSql = "SELECT effect_id,effect_name FROM {{%product_effect}}";
        $effectArr  = Yii::$app->db->createCommand($effectSql)->queryAll();
        $effect     = [];
        foreach ($effectArr as $k => $v) {
            $effect[$v['effect_name']] = $v['effect_id'];
        }

        //查产品
        $compSql    = "SELECT product_name FROM {{%product_details}} WHERE id = '$id'";
        $product_name  = Yii::$app->db->createCommand($compSql)->queryScalar();

        //查成份
        $compSql= "SELECT C.id,C.component_action
                FROM {{%product_relate}} R LEFT JOIN {{%product_component}}  C ON  R.component_id = C.id
                WHERE R.product_id = '$id'";
        $componentList  = Yii::$app->db->createCommand($compSql)->queryAll();

        //功效成份
        foreach ($componentList as $k => $v) {
            $component  = $v['component_action'];
            // $name       = $v['name'];
            $effectStr  = '';
            //匹配美白
            $rule1      = preg_match('/美白祛斑/is', $component);
            if($rule1) $effectStr .= $effectStr ? ',1' : '1';
            //匹配保湿
            $rule2      = preg_match('/保湿剂/is', $component);
            if($rule2) $effectStr .= $effectStr ? ',2' : '2';
            //匹配舒缓抗敏
            $rule3      = preg_match('/舒缓抗敏/is', $component);
            if($rule3) $effectStr .= $effectStr ? ',3' : '3';
            //匹配去角质
            $rule4      = preg_match('/去角质/is', $component);
            if($rule4) $effectStr .= $effectStr ? ',4' : '4';
            //匹配去抗皱
            $rule5      = preg_match('/抗氧化剂/is', $component);
            if($rule5) $effectStr .= $effectStr ? ',5' : '5';
            //匹配去黑头
            $rule6      = preg_match('/黑头/is', $product_name);
            if($rule6) $effectStr .= $effectStr ? ',6' : '6';
            //匹配抗痘
            $rule7      = preg_match('/痘|控油/is', $product_name);
            if($rule7) $effectStr .= $effectStr ? ',7' : '7';

            if($effectStr){
                $sql        = "UPDATE {{%product_details}} SET effect_id = '$effectStr' WHERE id = '$id'";
                Yii::$app->db->createCommand($sql)->execute();
            }
        }
        usleep(100);
        unset($componentList);
    }
    
    //批量分类上架
    public function actionBatchCate(){
        $post = Yii::$app->request->post();
        
        if ($post) {
            $cate_id = $post['cate_id'];
            $keyword = $post['keyword'];
            
            if (!empty($cate_id) && !empty($keyword)) {
                if (ProductCategory::findOne($cate_id)) {
                    $count = ProductDetails::find()->FilterWhere(['like', 'product_name', $keyword])->count();
                    $pageSize = 50;
                    $pageMin = 0;
                    $total = intval(ceil($count/$pageSize));
                    $num = 0;

                    if (!empty($count)) {                        
                        for($i=1;$i<=$total;$i++) {
                        
                            $sql = "SELECT id,product_name,cate_id,status FROM {{%product_details}} WHERE product_name LIKE '%{$keyword}%' LIMIT $pageMin,$pageSize";
                            $idArr = Yii::$app->db->createCommand($sql)->queryColumn();
                        
                            if (!empty($idArr)) {
                                foreach ($idArr as $key=>$val) {
                                    $update = "UPDATE {{%product_details}} SET cate_id = $cate_id,status = '1' WHERE id = $val";
                                    Yii::$app->db->createCommand($update)->execute();
                                    $num += 1;
                                }
                                $pageMin = $pageMin + 50;
                                usleep(100);
                            }
                        }
                        
                        $data['status'] = "1";
                        $data['num'] = $num;
                    }
                }
                
            } else {
                $data['status'] = "0";
            }    
            
            echo json_encode($data);
        } else {
            //分类
            $cateList = CommonFun::getCateList($where = 'parent_id <> 0');
            return $this->render('batch-cate',[
                'cateList' => $cateList,
            ]);
        }
    }
    
    //更新评论数（正式站用完删）
    public function actionUpdateCommentNum()
    {
        $count = ProductDetails::find()->where("comment_num <> 0")->count();
        $pageSize = 10;
        $pageMin = 0;
        $total = intval(ceil($count/$pageSize));
    
        for($i=1;$i<=$total;$i++) {
    
            $sql = "SELECT id FROM {{%product_details}} WHERE comment_num <> 0 limit $pageMin,$pageSize";
            $idArr = Yii::$app->db->createCommand($sql)->queryColumn();
    
            if (!empty($idArr)) {
                foreach ($idArr as $key=>$val) {
                    $ProductCommentUpdateSql  = " UPDATE {{%product_details}} P SET P.comment_num = (SELECT COUNT(*) FROM {{%comment}}  WHERE type = '1' AND post_id = '$val'  AND status = '1' AND parent_id = '0')  WHERE P.id = '$val'";
                    Yii::$app->db->createCommand($ProductCommentUpdateSql)->execute();
                }
                $pageMin = $pageMin + 10;
                usleep(100);
            }
        }
    }
    
    //抓取成分
    public function actionTakeComponent()
    {
        if ($post = Yii::$app->request->post()) {
            $name = $post['name'];
            $data = Tools::getProductComponent($name);
            
            $data['msg'] = $data['status'] ? array_reverse($data['msg']) :$data['msg'];
            if (empty($data['error'])) {
                $data['error'] = '';
            } else {
                $data['error'] = join(',', $data['error']);
            }

            return json_encode($data);
        }
    }
}
