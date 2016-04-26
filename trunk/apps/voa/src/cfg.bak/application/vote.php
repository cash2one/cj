<?php
/**
 * vote.php
 * 微评选菜单设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '评选列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'setting' => array(
		'icon' => 'fa-gear', 'name' => '设置', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	),

	'edit' => array(
		'icon' => 'fa-edit', 'name' => '编辑评选', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
	),
	'view' => array(
		'icon' => 'fa-eye', 'name' => '评选查看', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 104, 'default' => 0,
	),
	'verify' => array(
		'icon' => '', 'name' => '审核评选发起', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 105, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除评选', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 106, 'default' => 0,
	),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '发起评选',
		'url' => '{domain_url}/frontend/vote/new/?pluginid={pluginid}',
	),
	array(
		'name' => '查看评选',
		'sub_button' => array(
			array(
				'type' => 'view', 'name' => '进行中',
				'url' => '{domain_url}/frontend/vote/list/?status=unclosed&pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '已结束',
				'url' => '{domain_url}/frontend/vote/list/?status=fin&pluginid={pluginid}',
			),
		),
	),
);
