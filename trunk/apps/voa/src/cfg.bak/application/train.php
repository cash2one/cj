<?php
/**
 * train.php
 * 培训菜单设置
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'atlist' => array(
		'icon' => 'fa-list', 'name' => '文章列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 1021, 'default' => 1,
	),
	'atadd' => array(
		'icon' => 'fa-plus', 'name' => '添加文章', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 1022, 'default' => 0,
	),

	'cglist' => array(
		'icon' => 'fa-list', 'name' => '目录列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 1023, 'default' => 0,
	),
	'cgadd' => array(
		'icon' => 'fa-plus', 'name' => '添加目录', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 1024, 'default' => 0,
	),
	'atedit' => array(
			'icon' => 'fa-edit', 'name' => '编辑文章', 'display' => 1,
			'subnavdisplay' => 0, 'displayorder' => 1025, 'default' => 0,
	),

	'atdelete' => array(
			'icon' => 'fa-trash-o', 'name' => '删除文章', 'display' => 1,
			'subnavdisplay' => 0, 'displayorder' => 1026, 'default' => 0,
	),
	'atview' => array(
			'icon' => 'fa-eye', 'name' => '查看文章', 'display' => 1,
			'subnavdisplay' => 0, 'displayorder' => 1027, 'default' => 0,
	),
	'cgedit' => array(
			'icon' => 'fa-edit', 'name' => '编辑目录', 'display' => 1,
			'subnavdisplay' => 0, 'displayorder' => 1028, 'default' => 0,
	),
	'cgdelete' => array(
			'icon' => 'fa-trash-o', 'name' => '删除目录', 'display' => 1,
			'subnavdisplay' => 0, 'displayorder' => 1029, 'default' => 0,
	),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '培训中心',
		'url' => '{domain_url}/frontend/train/index?pluginid={pluginid}',
	),
);
