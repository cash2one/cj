<?php
/**
 * addressbook.php
 * 通讯录菜单设置
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
		'type' => 'view', 'name' => '通讯录',
		'url' => '{domain_url}/frontend/addressbook/list?pluginid={pluginid}',
	),
	array(
		'type' => 'view',
		'name' => '我的名片',
		'url' => '{domain_url}/frontend/addressbook/idcard?pluginid={pluginid}',
	),
);