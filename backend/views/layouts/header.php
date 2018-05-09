<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
?>

<header class="main-header">

    <?= Html::a('<span class="logo-mini">APP</span><span class="logo-lg">'. Html::img(Yii::$app->params['static_path'] . "h5/images/logo/58.png",['height' => '35px']) ."&nbsp;". Yii::$app->name . '</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">
            <?= Html::a(
                '欢迎您：' . Yii::$app->user->identity->username,
                ['/admins/update','id'=>Yii::$app->user->identity->id],
                ['style' => 'color:white;line-height:50px;padding:5px 10px;','title'=>'修改密码']
            ) ?>

            <?= Html::a(
                '<i class="glyphicon glyphicon-log-out"></i>&nbsp;&nbsp;退出',
                ['/site/logout'],
                ['data-method' => 'post', 'style' => 'color:white;line-height:50px;padding:5px 20px;']
            ) ?>

        </div>
    </nav>
</header>
