<?php
namespace frontend\controllers;

use Yii;
use frontend\controllers\BaseController;
use frontend\models\WebPage;
use common\functions\Tools;
use common\models\Article;
use common\models\ArticleCategory;


class ArticleController extends BaseController
{ 
    public function actionIndex($page = '1',$pageSize = '20',$cateId='',$keyword = '',$hotId = '')
    {
        $webPage = new WebPage();
        //分类列表
        $cateList = $webPage->getArticleCateList($has_child = 1);
        //文章列表
        $articleList = $webPage->getArticleList($page,$pageSize = '20',$cateId,$keyword,$hotId,$recommend = '',$orderBy = 'id desc');

        //无搜索结果，显示推荐文章
        $recommendArticleList = array();
        if(empty($articleList['list'])){
            $recommendArticleList = $webPage->getArticleList($page,$pageSize = '8',$cateId='',$keyword = '','',$recommend = '1',$orderBy = 'id desc');
        }

        //推荐产品
        $recommendProduct = $webPage->getRecommendProduct($page = '1',$pageSize = "5");

        //推荐品牌
        $recommendBrand = $webPage->getRecommendBrand(6);

        //广告列表
        $advertisementList = [
            "left1" => $webPage->getAdList($type = "article/index",$position = "left",$sort = "1"),
        ];

        if(($cateId)){
            $header = $this->getHeaderList($cateId);
            if (!empty($header)) {
                $this->GLOBALS['title'] = $header['title'];
                $this->GLOBALS['keywords'] = $header['keywords'];
            }
        }else if(($keyword)) {
            if(!empty($articleList['list'])){
                $this->GLOBALS['title'] = $keyword . '相关的文章-颜究院';
            }else{
                $this->GLOBALS['title'] = '找不到搜索词相关的文章-颜究院';
            }

        }else if(($hotId)) {
            $sql = "SELECT tagname FROM {{%common_tag}} WHERE tagid = '$hotId'";
            $tagname = Yii::$app->db->createCommand($sql)->queryScalar();

            if($tagname){
                $this->GLOBALS['title'] = $tagname . "-颜究院";
                $this->GLOBALS['keywords'] = $tagname;
            }
        }else{
            $this->GLOBALS['title'] = '科学护肤_健康护肤-颜究院';
        }

        return $this->renderPartial('index.htm',[
            'cateList' => $cateList,
            'articleList' => $articleList,
            'recommendArticleList' => $recommendArticleList,
            'recommendProduct' => $recommendProduct,
            'recommendBrand' => $recommendBrand,
            'advertisementList' => $advertisementList,
            'GLOBALS' => $this->GLOBALS,
            'Tools' => new Tools,
        ]);
    }

    public function actionDetails($id)
    {
        $webPage = new WebPage();
        //文章详情
        $details = $webPage->getArticleDetails($id);
        //一级分类列表
        $cateList = $webPage->getArticleCateList($has_child = 0);
        //产品搜索词列表
        $productWord = $webPage->getHotKeyword(20);
        //推荐产品
        $recommendProduct = $webPage->getRecommendProduct($page = '1',$pageSize = "5");

        $this->GLOBALS['title'] = $details['title'].'-颜究院';
        $this->GLOBALS['description'] = mb_substr(strip_tags($details['content']),0,100,"utf-8");
        $this->GLOBALS['keywords'] = $details['title'].'-颜究院';

        //广告列表
        $advertisementList = [
            "main1" => $webPage->getAdList($type = "article/details",$position = "main",$sort = "1"),
        ];
        //文章点击数
        Article::articleClick($id);
        
        return $this->renderPartial('details.htm',[
            'details' => $details,
            'cateList' => $cateList,
            'productWord' => $productWord,
            'recommendProduct' => $recommendProduct,
            'advertisementList' => $advertisementList,
            'GLOBALS' => $this->GLOBALS,
            'equipment' => $this->GLOBALS['equipment']
        ]);
    }

    //文章点击数
    static function articleClick($id){
        //点击数+1
        $model = Article::findOne($id);
        $time = strtotime("-7 days");

        if(empty($model->week_click_time) || ($model->week_click_time < $time)){
            $model->week_click = 1;
            $model->week_click_time = time();
        }else{
            $model->week_click = $model->week_click + 1;
        }
        $model->click_num = $model->click_num + 1;

        $model->save(false);
    }



    //header分类
    static function getHeaderList($id)
    {
        $headerList = [
            '1' => [
                'title' => '肤质判断_肤质分类_如何改善肤质-颜究院',
                'keywords' => '肤质判断，肤质分类，如何改善肤质',
            ],
            '2' => [
                'title' => '护肤品功效分类_护肤品一般有哪些功效-颜究院',
                'keywords' => '护肤品功效，护肤品功效分类，护肤品功效一般有哪些',
            ],
            '3' => [
                'title' => '护肤品评测-颜究院',
                'keywords' => '护肤品评测，护肤品测评，护肤品分析',
            ],
            '4' => [
                'title' => '明星护肤-颜究院',
                'keywords' => '明星护肤',
            ],
            '5' => [
                'title' => '护肤科普_常见护肤误区_护肤品成分分析-颜究院',
                'keywords' => '护肤科普，护肤误区，护肤品成分分析',
            ],
            '6' => [
                'title' => '干性皮肤_干性皮肤补水_干性皮肤怎么保养-颜究院',
                'keywords' => '干性皮肤，干性皮肤补水，干性皮肤怎么保养',
            ],
            '7' => [
                'title' => '油性皮肤_油性皮肤护理_油性皮肤怎么改善-颜究院',
                'keywords' => '油性皮肤，油性皮肤护理，油性皮肤怎么改善',
            ],
            '8' => [
                'title' => '中性皮肤_中性皮肤特点_中性皮肤保养-颜究院',
                'keywords' => '中性皮肤，中性皮肤特点，中性皮肤保养',
            ],
            '9' => [
                'title' => '混合性皮肤保养_混合性皮肤用什么护肤品-颜究院',
                'keywords' => '混合性皮肤，混合性皮肤保养，混合性皮肤用什么护肤品',
            ],
            '10' => [
                'title' => '敏感性皮肤的特点_敏感性皮肤保养_敏感性皮肤如何护理-颜究院',
                'keywords' => '敏感性皮肤的特点，敏感性皮肤保养，敏感性皮肤如何护理',
            ],
            '11' => [
                'title' => '如何美白提亮肤色_美白提亮护肤品有哪些-颜究院',
                'keywords' => '美白提亮，美白提亮护肤品，美白提亮肤色',
            ],
            '12' => [
                'title' => '如何补水保湿_补水保湿哪个牌子好-颜究院',
                'keywords' => '补水保湿，补水保湿产品，补水保湿效果好的护肤品，补水保湿哪个牌子好',
            ],
            '13' => [
                'title' => '防晒隔离使用顺序_防晒隔离护肤品哪个牌子好-颜究院',
                'keywords' => '防晒隔离，防晒隔离使用顺序，防晒隔离霜哪个牌子好',
            ],
            '14' => [
                'title' => '如何卸妆清洁_清洁卸妆步骤_卸妆用什么比较好-颜究院',
                'keywords' => '清洁卸妆，清洁卸妆步骤，卸妆用什么比较好',
            ],
            '15' => [
                'title' => '抗皱紧肤的方法_抗皱紧肤哪个品牌好-颜究院',
                'keywords' => '抗皱的方法，紧肤的方法，抗皱紧肤方法，抗皱紧肤哪个品牌好',
            ],
            '16' => [
                'title' => '怎么控油祛痘_控油祛痘方法_祛痘控油产品哪个好-颜究院',
                'keywords' => '如何控油祛痘，控油祛痘方法，祛痘控油产品哪个好',
            ],
            '17' => [
                'title' => '如何有效淡斑_淡斑的方法_淡斑产品哪个好-颜究院',
                'keywords' => '如何淡斑，淡斑方法，淡斑产品哪个好',
            ],
            '18' => [
                'title' => '如何去黑头_去黑头小窍门_去黑头最有效的方法-颜究院',
                'keywords' => '怎么去黑头，如何去黑头，去黑头小窍门，去黑头最有效的方法',
            ],
            '19' => [
                'title' => '如何去角质_去角质的步骤_去角质的正确方法-颜究院',
                'keywords' => '如何去角质，去角质的步骤，去角质的正确方法',
            ],
            '20' => [
                'title' => '如何去黑眼圈_去黑眼圈的方法_去黑眼圈小妙招-颜究院',
                'keywords' => '如何去黑眼圈，去黑眼圈的方法，去黑眼圈',
            ],
            '21' => [
                'title' => '护肤品新品评测-颜究院',
                'keywords' => '最新护肤品评测，最新护肤品测评，最新护肤品分析',
            ],
            '22' => [
                'title' => '护肤品评测精选-颜究院',
                'keywords' => '护肤品精选',
            ],
            '23' => [
                'title' => '明星用什么护肤品_明星护肤品有哪些_明星护肤品大公开-颜究院',
                'keywords' => '明星用什么护肤品，明星护肤品有哪些，明星护肤品大公开',
            ],
            '24' => [
                'title' => '明星护肤心得_明星怎么护肤_明星护肤方法-颜究院',
                'keywords' => '明星护肤心得，明星怎么护肤，明星护肤',
            ],
            '25' => [
                'title' => '大牌护肤品替代品_好用的平价护肤品-颜究院',
                'keywords' => '平价护肤品，好用的护肤品，大牌护肤替代品',
            ],
            '26' => [
                'title' => '去脂肪粒最好的方法_脂肪粒怎么去除-颜究院',
                'keywords' => '脂肪粒是怎么形成的，去脂肪粒最好的方法，脂肪粒怎么去除',
            ],
        ];
        
        $idArr = ArticleCategory::find()->select("id")->asArray()->column();
        if (in_array($id,$idArr) && !empty($headerList[$id])) {
            return $headerList[$id];
        } 
    }

}
