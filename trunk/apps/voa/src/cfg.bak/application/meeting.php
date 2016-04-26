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
		'icon' => 'fa-clock-o', 'name' => '会议列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'mrlist' => array(
		'icon' => 'fa-list', 'name' => '会议室列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	),
	'mradd' => array(
		'icon' => 'fa-plus', 'name' => '添加会议室', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 103, 'default' => 0,
	),

	'view' => array(
		'icon' => 'fa-eye', 'name' => '会议详情查看', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 104, 'default' => 0,
	),
	'mredit' => array(
		'icon' => 'fa-edit', 'name' => '编辑会议室', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 105, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除会议', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 106, 'default' => 0,
	),
	'mrdelete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除会议室', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 107, 'default' => 0,
	),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '发起会议', 'url' => '{domain_url}/frontend/meeting/new/?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '查询会议', 'url' => '{domain_url}/frontend/meeting/list/ac/join/?pluginid={pluginid}',
	),
	array(
		'type' => 'scancode_push', 'name' => '扫码签到', 'key' => 'meeting_sign',
	)
);
