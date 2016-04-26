<?php
/**
 * news.php
 * 通知公告菜单设置
 * Create By Yanwenzhong
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '公告列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'add' => array(
		'icon' => 'fa-plus', 'name' => '添加公告', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 102, 'default' => 0,
	),
	'edit' => array(
		'icon' => 'fa-edit', 'name' => '编辑公告', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除公告', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 104, 'default' => 0,
	),
	'view' => array(
		'icon' => 'fa-eye', 'name' => '查看公告', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 105, 'default' => 0,
	),
	'read' => array(
		'icon' => 'fa-eye', 'name' => '公告阅读人数', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 106, 'default' => 0,
	),
	'issue' => array(
		'icon' => 'fa-gear', 'name' => '权限设置', 'display' =>1,
		'subnavdisplay' => 1, 'displayorder'=> 107, 'default' => 0,
	),
	'madd' => array(
		'icon' => 'fa-plus', 'name' => '添加多条公告', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 108, 'default' =>0,
	),
	'templatelist' => array(
		'icon' => 'fa-plus', 'name' => '添加公告', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 109, 'default' =>0,
	),
	'category' => array(
		'icon' => 'fa-gear', 'name' => '菜单设置', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 110, 'default' =>0,
	),
	'addcategory' => array(
		'icon' => 'fa-edit', 'name' => '菜单修改', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 110, 'default' =>0,
	)
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '公司动态',
		'url' => '{domain_url}/frontend/news/list?nca_id=1&pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '通知公告',
		'url' => '{domain_url}/frontend/news/list?nca_id=2&pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '员工动态',
		'url' => '{domain_url}/frontend/news/list?nca_id=3&pluginid={pluginid}',
	)
);
