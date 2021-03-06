<?php
/**
 * inspect.php
 * 巡店菜单设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '巡店记录', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'plan' => array(
		'icon' => 'fa-list', 'name' => '巡店计划', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	),
	'shop' => array(
		'icon' => 'fa-list', 'name' => '门店管理', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 103, 'default' => 0,
	),
	'config' => array(
		'icon' => 'fa-list', 'name' => '评分配置', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 104, 'default' => 0,
	),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '巡店计划',
		'url' => '{domain_url}/frontend/inspect/tasklist/?pluginid={pluginid}',
	),
	array(
		'name' => '巡店记录', 'sub_button' => array(
			array(
				'type' => 'view', 'name' => '新建记录',
				'url' => '{domain_url}/frontend/inspect/tasknew/?pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '我收到的',
				'url' => '{domain_url}/frontend/inspect/list/ac/recv/?pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '我发出的',
				'url' => '{domain_url}/frontend/inspect/list/ac/mine/?pluginid={pluginid}',
			),
		),
	),
	array(
		'type' => 'view', 'name' => '门店排行',
		'url' => '{domain_url}/frontend/inspect/rank/?pluginid={pluginid}',
	),
);
