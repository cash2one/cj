<?php
/**
 * superreport.php
 * 培训菜单设置
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '数据列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 1021, 'default' => 1,
	),
	/* 'template' => array(
		'icon' => 'fa-plus', 'name' => '新建报表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 1022, 'default' => 0,
	), */

	'config' => array(
		'icon' => 'fa-list', 'name' => '报表设置', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 1023, 'default' => 0,
	),

	'view' => array(
			'icon' => 'fa-eye', 'name' => '查看报表', 'display' => 1,
			'subnavdisplay' => 0, 'displayorder' => 1025, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除报表', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 1026, 'default' => 0,
	),
	'add' => array(
		'icon' => 'fa-plus', 'name' => '添加模板', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 1028, 'default' => 0,
	),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '发送报表',
		'url' => '{domain_url}/frontend/superreport/index/?pluginid={pluginid}&__view=add',
	),
	array(
		'type' => 'view', 'name' => '查看日报',
		'url' => '{domain_url}/frontend/superreport/index/?pluginid={pluginid}&__view=daily',
	),
	array(
		'type' => 'view', 'name' => '查看月报',
		'url' => '{domain_url}/frontend/superreport/index/?pluginid={pluginid}&__view=month',
	)
);

