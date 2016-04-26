<?php
/**
 * 问卷调查
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '问卷列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'addedit' => array(
		'icon' => 'fa-plus', 'name' => '新增问卷', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除问卷', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
	),
	'view' => array(
		'icon' => 'fa-eye', 'name' => '问卷详情', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 104, 'default' => 0,
	),
	'close' => array(
		'icon' => 'fa-trash-o', 'name' => '关闭问卷', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 105, 'default' => 0,
	),
	'setting' => array(
		'icon' => 'fa-gear', 'name' => '相关设置', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 105, 'default' => 0,
	),
	'classify' => array(
		'icon' => 'fa-gear', 'name' => '分类设置', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 105, 'default' => 0,
	),
	'situation' => array(
		'icon' => 'fa-gear', 'name' => '填写详情', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 105, 'default' => 0,
	),

);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '问卷列表',
		'url' => '{domain_url}/Questionnaire/Frontend/Index/Index',
	),
	array(
		'type' => 'view', 'name' => '我的问卷',
		'url' => '{domain_url}/Questionnaire/Frontend/My/Index',
	),
);