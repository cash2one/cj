<?php
/**
 * askfor.php
 * 审批菜单设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '审批列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'template' => array(
		'icon' => 'fa-list', 'name' => '审批流程', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	),
	/* 'setting' => array(
		'icon' => 'fa-gear', 'name' => '设置', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	), */

	'view' => array(
		'icon' => 'fa-eye', 'name' => '审批详情', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除审批', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 104, 'default' => 0,
	),
	'addtemplate' => array(
		'icon' => 'fa-plus', 'name' => '添加审批流程', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 105, 'default' => 0,
	),
	'edittemplate' => array(
		'icon' => 'fa-edit', 'name' => '编辑审批流程', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 106, 'default' => 0,
	),
	'deletetemplate' => array(
		'icon' => 'fa-trash-o', 'name' => '删除审批流程', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 107, 'default' => 0,
	),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '发起审批',
		'url' => '{domain_url}/frontend/askfor/template/?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '我收到的',
		'url' => '{domain_url}/frontend/askfor/list/?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '我发送的',
		'url' => '{domain_url}/frontend/askfor/record/?pluginid={pluginid}',
	),
);