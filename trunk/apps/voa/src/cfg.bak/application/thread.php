<?php
/**
 * _secret.php
 * 秘密社区菜单设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
    'new' => array(
        'icon' => 'fa-gear', 'name' => '新建话题', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 0
    ),
	'list' => array(
		'icon' => 'fa-list', 'name' => '社区管理', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 1,
	),
    'setting' => array(
        'icon' => 'fa-gear', 'name' => '社区设置', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 103, 'default' => 0
    ),
	'search' => array(
		'icon' => 'fa-search', 'name' => '搜索', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 104, 'default' => 0,
	),
	'view' => array(
		'icon' => 'fa-eye', 'name' => '详情', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 105, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 106, 'default' => 0,
	)
 
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
    array(
        'type' => 'view', 'name' => '发表话题', 'url' => '{domain_url}/frontend/thread/newthread/?pluginid={pluginid}',
    ),
    array(
        'name' => '查看话题', 'sub_button' => array(
            array(
                'type' => 'view', 'name' => '热门话题', 'url' => '{domain_url}/frontend/thread/index/ac/hot/?pluginid={pluginid}',
            ),
            array(
                'type' => 'view', 'name' => '精选话题', 'url' => '{domain_url}/frontend/thread/index/ac/choice/?pluginid={pluginid}',
            ),
            array(
                'type' => 'view', 'name' => '所有话题', 'url' => '{domain_url}/frontend/thread/index/ac/all/?pluginid={pluginid}',
            ),
        ),
    ),
    array(
        'type' => 'view', 'name' => '我的社区', 'url' => '{domain_url}/frontend/thread/index/ac/mine/?pluginid={pluginid}',
    ),
);
