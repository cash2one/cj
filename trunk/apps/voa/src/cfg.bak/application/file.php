<?php
/**
 * file.php
 * 文件菜单设置
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(

);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
	array(
		'type' => 'view', 'name' => '文件资料', 'url' => '{domain_url}/File/Frontend/Group/Group_list',
	)
);
