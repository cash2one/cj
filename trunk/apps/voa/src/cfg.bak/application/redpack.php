<?php
/**
 * redpack.php
 * 红包菜单设置
 *
 * $Author$
 * $Id$
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
	'setting' => array(
		'icon' => 'fa-gear',
		'name' => '设置',
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
	)
);
// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view',
		'name' => '发红包',
		'url' => '{domain_url}/frontend/redpack/new/?pluginid={pluginid}'
	),
	array(
		'type' => 'view',
		'name' => '我的红包',
		'url' => '{domain_url}/frontend/redpack/list/?pluginid={pluginid}'
	)
);
