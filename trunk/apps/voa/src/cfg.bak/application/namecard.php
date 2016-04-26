<?php
/**
 * namecard.php
 * 名片夹菜单设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '名片夹列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'folder' => array(
		'icon' => 'fa-list', 'name' => '群组列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	),
	'company' => array(
		'icon' => 'fa-list', 'name' => '公司列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 103, 'default' => 0,
	),
	'job' => array(
		'icon' => 'fa-list', 'name' => '职务列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 104, 'default' => 0,
	),

	'edit' => array(
		'icon' => 'fa-edit', 'name' => '编辑名片夹', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 105, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-times', 'name' => '删除名片夹', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 106, 'default' => 0,
	),
	'companyedit' => array(
		'icon' => 'fa-edit', 'name' => '编辑/删除公司', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 107, 'default' => 0,
	),
	'jobedit' => array(
		'icon' => 'fa-edit', 'name' => '编辑/删除职务', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 108, 'default' => 0,
	),
	'folderedit' => array(
		'icon' => 'fa-edit', 'name' => '编辑/删除群组', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 109, 'default' => 0,
	),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '加名片',
		'url' => '{domain_url}/frontend/namecard/new/?pluginid={pluginid}',
	),
	array(
		'name' => '找名片',
		'sub_button' => array(
			array(
				'type' => 'view', 'name' => '按姓名',
				'url' => '{domain_url}/frontend/namecard/list/ac/ascii/?pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '按近期',
				'url' => '{domain_url}/frontend/namecard/list/ac/created/?pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '按分组', 'url' =>
				'{domain_url}/frontend/namecard/list/ac/folder/?pluginid={pluginid}',
			),
		),
	),
	array(
		'type' => 'view', 'name' => '分组管理', 'url' =>
		'{domain_url}/frontend/namecard/folder_list/?pluginid={pluginid}',
	),
);
