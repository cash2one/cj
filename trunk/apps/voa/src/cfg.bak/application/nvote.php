<?php
/**
 * 投票调研
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午10:49
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
    'list' => array(
        'icon' => 'fa-list', 'name' => '投票列表', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
    ),

    'add' => array(
        'icon' => 'fa-plus', 'name' => '新增投票', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
    ),
    'delete' => array(
        'icon' => 'fa-trash-o', 'name' => '删除投票', 'display' => 1,
        'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
    ),
    'view' => array(
        'icon' => 'fa-eye', 'name' => '投票详情', 'display' => 1,
        'subnavdisplay' => 0, 'displayorder' => 104, 'default' => 0,
    ),
    'close' => array(
        'icon' => 'fa-trash-o', 'name' => '关闭投票', 'display' => 1,
        'subnavdisplay' => 0, 'displayorder' => 104, 'default' => 0,
    ),
    'issue' => array(
	    'icon' => 'fa-gear', 'name' => '设置权限', 'display' => 1,
	    'subnavdisplay' => 1, 'displayorder' => 106, 'default' => 0,
    ),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '创建投票',
		'url' => '{domain_url}/frontend/nvote/new/?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '投票列表',
		'url' => '{domain_url}/frontend/nvote/list/?pluginid={pluginid}',
	),
	array(
		'name' => '我的投票',
		'sub_button' => array(
			array(
				'type' => 'view', 'name' => '我发起的',
				'url' => '{domain_url}/frontend/nvote/my/?nvote=mine&pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '我参与的',
				'url' => '{domain_url}/frontend/nvote/my/?nvote=join&pluginid={pluginid}',
			),
		),
	),
);