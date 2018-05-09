<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
?>
<aside class="main-sidebar">

    <section class="sidebar">

<?php 
use mdm\admin\components\MenuHelper;
$callback = function($menu){
    $data = json_decode($menu['data'], true);
    $items = $menu['children']; 
    $return = [ 
    'label' => $menu['name'], 
    'url' => [
            $menu['route']
        ]
    ];
    // 处理我们的配置
    if ($data) {
        // visible
        isset($data['visible']) && $return['visible'] = $data['visible'];
        // icon
        isset($data['icon']) && $data['icon'] && $return['icon'] = $data['icon'];
        // other attribute e.g. class...
        $return['options'] = $data;
    }
    // 没配置图标的显示默认图标
    (! isset($return['icon']) || ! $return['icon']) && $return['icon'] = 'fa fa-circle-o';
    $items && $return['items'] = $items;
    return $return;
};
// 这里我们对一开始写的菜单menu进行了优化
echo dmstr\widgets\Menu::widget([
    'options' => [
        'class' => 'sidebar-menu'
    ],
    'items' => MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback),
] ); ?>


<!-- <ul class="sidebar-menu">
    <li class="treeview">
    <a href="#">
        <i class="fa fa-gears"></i> <span>权限控制</span>
        <i class="fa fa-angle-left pull-right"></i>
    </a>
        <ul class="treeview-menu">
            <li><a href="/admin/route"><i class="fa fa-circle-o"></i> 路由</a></li>
            <li><a href="/admin/permission"><i class="fa fa-circle-o"></i> 权限</a></li>
            <li><a href="/admin/role"><i class="fa fa-circle-o"></i> 角色</a></li>
            <li><a href="/admin/assignment"><i class="fa fa-circle-o"></i> 分配</a></li>
            <li><a href="/admin/menu"><i class="fa fa-circle-o"></i> 菜单</a></li>
        </ul>
    </li>
</ul> -->
</section>
</aside>

