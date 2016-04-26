<?php
/**
 * travel.php
 * 营销 CRM 配置
 *
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'main' => array(
		'icon' => 'fa-list', 'name' => '产品列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'customer' => array(
		'icon' => 'fa-list', 'name' => '客户列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	),
	'order' => array(
		'icon' => 'fa-list', 'name' => '订单列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 103, 'default' => 0,
	),
	'turnover' => array(
		'icon' => 'fa-list', 'name' => '业绩与提成', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 103, 'default' => 0,
	),
	'material' => array(
		'icon' => 'fa-list', 'name' => '专题库', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 104, 'default' => 0,
	),
	'diyindex' => array(
		'icon' => 'fa-list', 'name' => '自定义首页', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 104, 'default' => 0,
	)
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '我的主页',
		'url' => '{domain_url}/frontend/travel/cpindex?pluginid={pluginid}',
	)
);

