<?php
/**
 * vnote.php
 * 备忘录菜单设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array();

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '新建备忘',
		'url' => '{domain_url}/frontend/vnote/new/?pluginid={pluginid}',
	),
	array(
		'type' => 'view', 'name' => '查看备忘',
		'url' => '{domain_url}/frontend/vnote/search/?pluginid={pluginid}',
	),
);
