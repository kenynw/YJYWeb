<?php

require './taobao-sdk/TopSdk.php';

$appKey = '24577978';
$appSecret = 'de94a61849f1a3391cb99c1a1e880d7e';
$adzoneId = '99532920';
$title = isset($_GET['title']) ? $_GET['title'] : null;
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : null;

// 无商品标题参数

if (!$title) {
  $data = [
    'message' => 'missing param: title',
  ];

  echo json_encode($data);
  exit;
}

// 查询优惠券

$c = new TopClient;
$c->appkey = $appKey;
$c->secretKey = $appSecret;
$req = new TbkDgItemCouponGetRequest;
$req->setAdzoneId($adzoneId);
$req->setQ($title);
$coupon = $c->execute($req)->results->tbk_coupon;

// 没有优惠券

if (empty($coupon)) {
  $data = [
    'message' => 'no coupon',
  ];

  echo json_encode($data);
  exit;
}

// 返回优惠券 JSON

if (!$redirect) {
  echo json_encode($coupon);
  exit;
}

// 跳转到优惠券

header("Location: {$coupon->coupon_click_url}", true, 301);
exit;
