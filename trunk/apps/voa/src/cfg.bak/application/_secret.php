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
	'list' => array(
		'icon' => 'fa-list', 'name' => '秘密列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'search' => array(
		'icon' => 'fa-search', 'name' => '搜索', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	),
	'view' => array(
		'icon' => 'fa-eye', 'name' => '浏览', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 104, 'default' => 0,
	),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array();
