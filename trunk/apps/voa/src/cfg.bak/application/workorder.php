<?php
/**
 * workorder.php
 * 移动派单菜单配置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '全部工单', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'search' => array(
		'icon' => 'fa-search', 'name' => '工单搜索', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	),

	'view' => array(
		'icon' => 'fa-eye-o', 'name' => '工单详情', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
	),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '发起工单',
		'url' => '{domain_url}/frontend/workorder/index?pluginid={pluginid}&__view=publish',
	),
	array(
		'type' => 'view', 'name' => '我的工单',
		'url' => '{domain_url}/frontend/workorder/index?pluginid={pluginid}&__view=list&__params[type]=wait_confirm&__params[res]=received',
	),
);
