<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = '颜究院后台';

$fieldOptions1 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-envelope form-control-feedback'></span>"
];

$fieldOptions2 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-lock form-control-feedback'></span>"
];
?>

<style>
    .login{
        margin: 150px auto 0 auto;
        min-height: 420px;
        max-width: 420px;
        padding: 40px;
        background-color: #ffffff;
        margin-left: auto;
        margin-right: auto;
        border-radius: 4px;
        /* overflow-x: hidden; */
        box-sizing: border-box;
    }
    a.logo{
        display: block;
        height: 58px;
        width: 167px;
        margin: 0 auto 30px auto;
        background-size: 167px 42px;
    }
    .message {
        margin: 10px 0 0 -58px;
        padding: 18px 10px 18px 60px;
        background: #27A9E3;
        position: relative;
        color: #fff;
        font-size: 16px;
    }
    #darkbannerwrap {
        background: url(/images/aiwrap.png);
        width: 18px;
        height: 10px;
        margin: 0 0 20px -58px;
        position: relative;
    }

    input[type=text],
    input[type=file],
    input[type=password],
    input[type=email], select {
        border: 1px solid #DCDEE0;
        vertical-align: middle;
        border-radius: 3px;
        height: 50px;
        padding: 0px 16px;
        font-size: 14px;
        color: #555555;
        outline:none;
        width:100%;
    }
    input[type=text]:focus,
    input[type=file]:focus,
    input[type=password]:focus,
    input[type=email]:focus, select:focus {
        border: 1px solid #27A9E3;
    }


    input[type=submit],
    input[type=button]{
        display: inline-block;
        vertical-align: middle;
        padding: 12px 24px;
        margin: 0px;
        font-size: 18px;
        line-height: 24px;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        cursor: pointer;
        color: #ffffff;
        background-color: #27A9E3;
        border-radius: 3px;
        border: none;
        -webkit-appearance: none;
        outline:none;
        width:100%;
    }
    hr.hr15 {
        height: 15px;
        border: none;
        margin: 0px;
        padding: 0px;
        width: 100%;
    }
    hr.hr20 {
        height: 20px;
        border: none;
        margin: 0px;
        padding: 0px;
        width: 100%;
    }

</style>

<div class="login">
    <div class="message">颜究院-管理登录</div>
    <div id="darkbannerwrap"></div>

    <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => false]); ?>

        <input name="action" value="login" type="hidden">
        <input name="LoginForm[username]" placeholder="用户名" required="" type="text">
        <hr class="hr15">
        <input name="LoginForm[password]" placeholder="密码" required="" type="password">
        <span style="color:red;font-size:12px;"><?php echo $msg ?></span>
        <hr class="hr15">
        <input value="登录" style="width:100%;" type="submit">
        <hr class="hr20">

    <?php ActiveForm::end(); ?>
</div>

