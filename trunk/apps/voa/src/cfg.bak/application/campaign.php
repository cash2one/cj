<?php
/**
 * campaign.php
 * 活动推广菜单设置
 * Create By linshiling
 * $Author$
 * $Id$
 */


// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'add' => array(
		'icon' => 'fa-list', 'name' => '新增活动', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 0,
	),
	'list' => array(
		'icon' => 'fa-list', 'name' => '活动列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 1,
	),
	'total' => array(
		'icon' => 'fa-plus', 'name' => '数据中心', 'display' => 0,
		'subnavdisplay' => 1, 'displayorder' => 103, 'default' => 0,
	),
	'setting' => array(
		'icon' => 'fa-plus', 'name' => '设置', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 104, 'default' => 0,
	),
	'edit' => array(
		'icon' => 'fa-edit', 'name' => '编辑活动', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 105, 'default' => 0,
	),
	'view' => array(
		'icon' => 'fa-eye', 'name' => '查看活动', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 106, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除活动', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 107, 'default' => 0,
	),
	'typeedit' => array(
		'icon' => 'fa-edit', 'name' => '编辑分类', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 108, 'default' => 0,
	),
	'typedelete' => array(
		'icon' => 'fa-edit', 'name' => '删除分类', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 109, 'default' => 0,
	)
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '活动中心',
		'url' => '{domain_url}/frontend/campaign/list?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '客户列表',
		'url' => '{domain_url}/frontend/campaign/cuserlist?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '数据跟踪',
		'url' => '{domain_url}/frontend/campaign/total?pluginid={pluginid}',
	),
);
