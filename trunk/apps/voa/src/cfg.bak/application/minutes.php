<?php
/**
 * minutes.php
 * 会议记录菜单设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '会议记录列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),

	'view' => array(
		'icon' => 'fa-eye', 'name' => '浏览会议记录', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 102, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除会议记录', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
	),
	'setting' => array(
		'icon' => 'fa-gear', 'name' => '设置', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 104, 'default' => 0
	)
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '新建记录',
		'url' => '{domain_url}/frontend/minutes/new/?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '查询记录',
		'url' => '{domain_url}/frontend/minutes/search/?pluginid={pluginid}',
	),
);
