<?php
/**
 * jobtrain.php
 * 培训菜单设置
 * Create By wowxavi
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '知识管理', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'add' => array(
		'icon' => 'fa-plus', 'name' => '添加内容', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	),
	'catalist' => array(
		'icon' => 'fa-gear', 'name' => '分类设置', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 103, 'default' => 0,
	),
	'cataadd' => array(
		'icon' => 'fa-plus', 'name' => '添加分类', 'display' => 0,
		'subnavdisplay' => 0, 'displayorder' => 104, 'default' => 0,
	),
	'cataedit' => array(
		'icon' => 'fa-edit', 'name' => '编辑分类', 'display' => 0,
		'subnavdisplay' => 0, 'displayorder' => 105, 'default' => 0,
	),
	'cataview' => array(
		'icon' => 'fa-eye', 'name' => '查看分类', 'display' => 0,
		'subnavdisplay' => 0, 'displayorder' => 106, 'default' => 0,
	),
	'catadel' => array(
		'icon' => 'fa-trash-o', 'name' => '删除分类', 'display' => 0,
		'subnavdisplay' => 0, 'displayorder' => 107, 'default' => 0,
	),
	'edit' => array(
		'icon' => 'fa-edit', 'name' => '编辑内容', 'display' => 0,
		'subnavdisplay' => 0, 'displayorder' => 108, 'default' => 0,
	),
	'view' => array(
		'icon' => 'fa-eye', 'name' => '知识详情', 'display' => 0,
		'subnavdisplay' => 0, 'displayorder' => 109, 'default' => 0,
	),
	'del' => array(
		'icon' => 'fa-trash-o', 'name' => '删除内容', 'display' => 0,
		'subnavdisplay' => 0, 'displayorder' => 110, 'default' => 0,
	),
	'colllist' => array(
		'icon' => 'fa-eye', 'name' => '收藏人数', 'display' => 0,
		'subnavdisplay' => 0, 'displayorder' => 111, 'default' => 0,
	),
	'collexport' => array(
		'icon' => 'fa-list', 'name' => '导出收藏', 'display' => 0,
		'subnavdisplay' => 0, 'displayorder' => 112, 'default' => 0,
	),
	'studylist' => array(
		'icon' => 'fa-eye', 'name' => '学习人数', 'display' => 0,
		'subnavdisplay' => 0, 'displayorder' => 113, 'default' => 0,
	),
	'studyexport' => array(
		'icon' => 'fa-list', 'name' => '导出学习', 'display' => 0,
		'subnavdisplay' => 0, 'displayorder' => 114, 'default' => 0,
	)
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '知识管理',
		'url' => '{domain_url}/Jobtrain/Frontend/Index/ArticleList',
	),
	array(
		'type' => 'view', 'name' => '我的收藏',
		'url' => '{domain_url}/Jobtrain/Frontend/Index/CollList',
	)
);