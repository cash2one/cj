<?php
/**
 * _example.php
 * 应用菜单配置文档举例 // 这里标记该配置属于哪个应用
 * Create By Deepseath
 * $Author$
 * $Id$
 */

/**
 * 前台菜单设置
 * 暂时尚未实际部署，但必须定义
 */
// 前台菜单配置
$conf['menu.frontend'] = array();

/**
 * 应用的后台菜单配置，根据应用具体情况而定，每个菜单一个数组
 * + 菜单subop
 * 	+ icon 菜单图标，可(推荐)使用 Font awesome图标（参看：http://fontawesome.io/icons/）
 *	   也可以使用自定义的图片文件(不推荐)
 *	+ name 菜单项的名称，建议使用8个字符以内
 *	+ display 是否启用(显示)菜单,1=启用,0=不启用,则任何人无法使用该功能
 *	+ subnavdisplay 是否在子导航内显示菜单。1=显示,0=不显示。一般类似edit/delete/view等操作可设置为0
 *	+ displayorder 菜单在该应用的显示顺序，正整数，0到9999范围。推荐使用101开始。
 *	+ default 该菜单项是否为应用的默认菜单（功能）。1=是，0=否。一般推荐数据列表项为1，其他设置为0
 */
// 后台菜单配置
$conf['menu.admincp'] = array(
	'list' => array(
		'icon' => 'fa-list', 'name' => '审批列表', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
	),
	'setting' => array(
		'icon' => 'fa-gear', 'name' => '设置', 'display' => 1,
		'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
	),

	'view' => array(
		'icon' => 'fa-eye', 'name' => '审批详情', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 103, 'default' => 0,
	),
	'delete' => array(
		'icon' => 'fa-trash-o', 'name' => '删除审批', 'display' => 1,
		'subnavdisplay' => 0, 'displayorder' => 104, 'default' => 0,
	),
);

/**
 * 定义微信企业号的应用自定义菜单
 * 只允许建立建立最多两级菜单
 * 每级菜单为1到5个数组，结构均为：
 * array(
 * 	'type'=>view|click,
 * 	'name'=>string,
 * 	'url' => type为view时,
 * 	'key' => type为click时,
 * 	'sub_button' => 下级菜单
 * )
 * 主菜单标题name长不能超过16个字节，子菜单标题name不能超过40字节
 * 链接可使用两个字符串变量：{domain_url}表示域名主机头，{pluginid}表示插件的cp_id
 * 例如：
 * arra(
 * 	array('type' => 'view', 'name' => '第一级链接浏览型菜单','url'=>'点击菜单后跳转的url'),
 * 	array('type' => 'click', 'name' => '第一级响应Key值型菜单', 'key' => '发出的响应Key值'),
 * 	array('name'=> '我有下级菜单', 'sub_button' => array(
 * 		array('type' => 'view', 'name' => '二级链接1', 'url'=> '....'),
 *  	array('type' => 'view', 'name' => '二级链接2', 'url'=> '....'),
 * 		... ...)
 * 	),
 * )
 */
// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '发起审批',
		'url' => '{domain_url}/frontend/askfor/new/?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '审批记录',
		'url' => '{domain_url}/frontend/askfor/list/?pluginid={pluginid}',
	),
	array(
		'name' => '审批记录',
		'sub_button' => array(
			array(
				'type' => 'view', 'name' => '待我审批',
				'url' => '{domain_url}/frontend/askfor/list/ac/deal/?pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '我已审批',
				'url' => '{domain_url}/frontend/askfor/list/ac/done/?pluginid={pluginid}',
			),
		),
	),
);
