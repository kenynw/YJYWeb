<?php
use yii\widgets\Breadcrumbs;
use dmstr\widgets\Alert;

?>

<?= Breadcrumbs::widget(['links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],]) ?>

<div class="content-wrapper" style="background: #E7EAF3;padding:15px 20px;">

    <div style="background: white;">
        <section class="content" style="border-top:3px solid #35A8F9;">
            <?= Alert::widget() ?>
            <?= $content ?>
        </section>
    </div>

</div>

<footer class="main-footer">
    <div class="pull-right hidden-xs">
        <b>Version</b> 2.0
    </div>
    <strong>Copyright &copy; 2014-2015 <a href="http://almsaeedstudio.com">Almsaeed Studio</a>.</strong> All rights
    reserved.
</footer>
