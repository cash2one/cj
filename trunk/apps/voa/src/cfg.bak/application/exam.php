<?php
/**
 * activity.php
 * 活动报名菜单设置
 * Create By tim_zhang
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置 试卷管理、新建试卷、题库管理、新建题库、考试统计
$conf['menu.admincp'] = array(
	'paperlist' => array(
		'icon' => 'fa-list', 'name' => '试卷管理', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'addpaper' => array(
		'icon' => 'fa-plus', 'name' => '新建试卷', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	),
	'tikulist' => array(
		'icon' => 'fa-list', 'name' => '题库管理', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 103, 'default' => 0,
	),
	'addtiku' => array(
		'icon' => 'fa-plus', 'name' => '新建题库', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 104, 'default' => 0,
	),
	'tj' => array(
		'icon' => 'fa-bar-chart-o', 'name' => '考试统计', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 105, 'default' => 0,
	),
	'viewpaper' => array(
		'icon' => 'fa-plus', 'name' => '查看详情', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 106, 'default' => 0,
	),
	'deletepaper' => array(
		'icon' => 'fa-trash-o', 'name' => '删除试卷', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 107, 'default' => 0,
	),
	'stoppaper' => array(
		'icon' => 'fa-ban', 'name' => '提前终止', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 108, 'default' => 0,
	),
	'paperdetail' => array(
		'icon' => 'fa-plus', 'name' => '选择题目', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 109, 'default' => 0,
	),
	'papersetting' => array(
		'icon' => 'fa-plus', 'name' => '设置试卷', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 110, 'default' => 0,
	),
	'paperpreview' => array(
		'icon' => 'fa-plus', 'name' => '试卷预览', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 111, 'default' => 0,
	),
	'tjdetail' => array(
		'icon' => 'fa-eye', 'name' => '统计详情', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 112, 'default' => 0,
	),
	'tjnotify' => array(
		'icon' => 'fa-list', 'name' => '考试提醒', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 113, 'default' => 0,
	),
	'tjexport' => array(
		'icon' => 'fa-list', 'name' => '导出统计', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 114, 'default' => 0,
	),
	'deletetiku' => array(
		'icon' => 'fa-trash-o', 'name' => '删除题库', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 115, 'default' => 0,
	),
	'addtm' => array(
		'icon' => 'fa-plus', 'name' => '添加题目', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 116, 'default' => 0,
	),
	'viewtm' => array(
		'icon' => 'fa-eye', 'name' => '查看题目', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 117, 'default' => 0,
	),
	'deletetm' => array(
		'icon' => 'fa-trash-o', 'name' => '删除题目', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 118, 'default' => 0,
	),
	'viewanswer' => array(
		'icon' => 'fa-eye', 'name' => '查看答卷', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 119, 'default' => 0,
	)
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '考试中心',
		'url' => '{domain_url}/Exam/Frontend/Index/PaperList',
	)
);