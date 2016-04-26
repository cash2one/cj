<?php
/**
 * blessingredpack.php
 * 祝福红包菜单设置
 * @author      anything2
 * @version     $Id$
 */
// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
    'list' => array(
        'icon' => 'fa-list',
        'name' => '红包列表',
        'display' => 1,
        'subnavdisplay' => 1,
        'displayorder' => 101,
        'default' => 1
    ),
    'add' => array(
        'icon' => 'fa-plus',
        'name' => '添加活动',
        'display' => 1,
        'subnavdisplay' => 1,
        'displayorder' => 102,
        'default' => 0
    ),
    'view' => array(
        'icon' => 'fa-eye',
        'name' => '红包详情查看',
        'display' => 1,
        'subnavdisplay' => 0,
        'displayorder' => 103,
        'default' => 0
    ),
    'delete' => array(
        'icon' => 'fa-trash-o',
        'name' => '删除红包',
        'display' => 1,
        'subnavdisplay' => 0,
        'displayorder' => 104,
        'default' => 0
    ),
    'setting' => array(
        'icon' => 'fa-gear',
        'name' => '红包配置',
        'display' => 1,
        'subnavdisplay' => 1,
        'displayorder' => 105,
        'default' => 0
    )
);
//sss
// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
//    array(
//        'type' => 'view',
//        'name' => '我的红包',
//        'url' => '{domain_url}/frontend/blessingredpack/list/?pluginid={pluginid}'
//    )
);




