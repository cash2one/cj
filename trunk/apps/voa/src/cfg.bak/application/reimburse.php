<?php
/**
 * reimburse.php
 * 报销菜单设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '报销列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'setting' => array(
		'icon' => 'fa-gear', 'name' => '设置', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	),

	'view' => array(
		'icon' => 'fa-edit', 'name' => '报销详情', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除报销', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 104, 'default' => 0,
	),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '填写明细',
		'url' => '{domain_url}/frontend/reimburse/bill_new/?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '新建报销',
		'url' => '{domain_url}/frontend/reimburse/new/?pluginid={pluginid}',
	),
	array(
		'name' => '查询报销',
		'sub_button' => array(
			array(
				'type' => 'view', 'name' => '我发起的',
				'url' => '{domain_url}/frontend/reimburse/search/?ac=mine&pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '待我审批',
				'url' => '{domain_url}/frontend/reimburse/search/?ac=dealing&pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '我已审批',
				'url' => '{domain_url}/frontend/reimburse/search/?ac=dealed&pluginid={pluginid}',
			),
		),
	),
);
