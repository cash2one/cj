<?php
/**
 * wxwall.php
 * 微信墙菜单设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '微信墙列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'setting' => array(
		'icon' => 'fa-gear', 'name' => '设置', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	),

	'view' => array(
		'icon' => 'fa-eye', 'name' => '查看并审核内容', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
	),
	'edit' => array(
		'icon' => 'fa-edit', 'name' => '编辑微信墙', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 104, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除微信墙', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 105, 'default' => 0,
	),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '新建微信墙',
		'url' => '{domain_url}/wxwall/new/?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '微信墙列表',
		'url' => '{domain_url}/wxwall/list/?pluginid={pluginid}',
	),
);
