<?php
/**
 * footprint.php
 * 轨迹菜单设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '轨迹列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),

	'view' => array(
		'icon' => 'fa-eye', 'name' => '轨迹详情', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 102, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除轨迹', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
	),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '新建轨迹',
		'url' => '{domain_url}/frontend/footprint/new/?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '团队轨迹',
		'url' => '{domain_url}/frontend/footprint/team/?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '我的轨迹',
		'url' => '{domain_url}/frontend/footprint/mine/?pluginid={pluginid}',
	),
);
