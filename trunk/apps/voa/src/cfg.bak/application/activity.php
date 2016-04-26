<?php
/**
 * activity.php
 * 活动报名菜单设置
 * Create By tim_zhang
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '活动列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'view' => array(
		'icon' => 'fa-edit', 'name' => '活动详情', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 102, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除活动', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
	),
	'add' => array(
		'icon' => 'fa-plus', 'name' => '添加活动', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 104, 'default' => 0,
	),
	'edit' => array(
		'icon' => 'fa-edit', 'name' => '编辑活动', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 105, 'default' => 0,
	),
	'issue' => array(
		'icon' => 'fa-gear', 'name' => '权限设置', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 106, 'default' => 0,
	)
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '发起活动',
		'url' => '{domain_url}/frontend/activity/new/?pluginid={pluginid}'
	),
	array(
		'type' => 'view', 'name' => '查看活动',
		'url' => '{domain_url}/frontend/activity/list/?action=all&pluginid={pluginid}',
	),
	array(
		'name' => '我的活动',
		'sub_button' => array(
			array(
				'type' => 'view', 'name' => '我发起的',
				'url' => '{domain_url}/frontend/activity/list/?action=mine&pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '我参与的',
				'url' => '{domain_url}/frontend/activity/list/?action=join&pluginid={pluginid}',
			),
		),
	),
);
