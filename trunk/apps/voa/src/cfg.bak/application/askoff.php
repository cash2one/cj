<?php
/**
 * askoff.php
 * 请假菜单设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '请假列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'setting' => array(
		'icon' => 'fa-gear', 'name' => '设置', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	),

	'view' => array(
		'icon' => 'fa-eye', 'name' => '请假详情', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除请假', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 104, 'default' => 0,
	),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '发起请假',
		'url' => '{domain_url}/frontend/askoff/new/?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '请假记录',
		'url' => '{domain_url}/frontend/askoff/list/?pluginid={pluginid}',
	),
	array(
		'name' => '审批记录', 'sub_button' => array(
			array(
				'type' => 'view', 'name' => '待我审批',
				'url' => '{domain_url}/frontend/askoff/deal/?status=doing&pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '我已审批',
				'url' => '{domain_url}/frontend/askoff/deal/?status=done&pluginid={pluginid}',
			),
		),
	),
);
