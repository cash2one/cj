<?php
/**
 * plan.php
 * 日程菜单设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '日程列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),

	'view' => array(
		'icon' => 'fa-eye', 'name' => '日程详情', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 102, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除日程', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
	),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '新建日程',
		'url' => '{domain_url}/frontend/plan/new/?pluginid={pluginid}',
	),
	array(
		'name' => '查询日程',
		'sub_button' => array(
			array(
				'type' => 'view', 'name' => '我的日程',
				'url' => '{domain_url}/frontend/plan/list/?pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '我收到的',
				'url' => '{domain_url}/frontend/plan/share/?pluginid={pluginid}',
			),
		),
	),
);
