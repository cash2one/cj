<?php
/**
 * meeting.php
 * 会议菜单设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-clock-o', 'name' => '快递列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'view' => array(
		'icon' => 'fa-eye', 'name' => '快递详情', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 102, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除', 'display' => 1,
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
		'type' => 'view', 'name' => '快递登记', 'url' => '{domain_url}/frontend/express/new/?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '快递列表', 'url' => '{domain_url}/frontend/express/list/ac/join/?pluginid={pluginid}',
	),
	array(
		'type' => 'scancode_push', 'name' => '签收确认', 'key' => 'express_scan',
	)
);
