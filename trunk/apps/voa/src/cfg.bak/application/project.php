<?php
/**
 * project.php
 * 任务菜单设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '任务列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'setting' => array(
		'icon' => 'fa-gear', 'name' => '设置', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	),

	'view' => array(
		'icon' => 'fa-eye', 'name' => '任务详情查看', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除任务', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 104, 'default' => 0,
	),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '发起任务',
		'url' => '{domain_url}/frontend/project/new/?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '我的任务',
		'url' => '{domain_url}/frontend/project/list/?pluginid={pluginid}',
	),
);
