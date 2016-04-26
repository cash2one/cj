<?php
/**
 * invite.php
 *
 * Created by zhoutao.
 * Created Time: 2015/7/8  14:42
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list',
		'name' => '人员列表',
		'display' => 1,
		'subnavdisplay' => 1,
		'displayorder' => 101,
		'default' => 1,
	),
	'config' => array(
		'icon' => 'fa-gear',
		'name' => '邀请设置',
		'display' => 1,
		'subnavdisplay' => 1,
		'displayorder' => 101,
		'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o',
		'name' => '删除人员',
		'display' => 1,
		'subnavdisplay' => 0,
		'displayorder' => 104,
		'default' => 0,
	),
	'view' => array(
		'icon' => 'fa-eye',
		'name' => '人员详情',
		'display' => 1,
		'subnavdisplay' => 0,
		'displayorder' => 105,
		'default' => 0,
	),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '快速邀请', 'url' => '{domain_url}/frontend/invite/share/?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '我的邀请', 'url' => '{domain_url}/frontend/invite/list/?pluginid={pluginid}',
	)
);
