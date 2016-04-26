<?php
/**
 * xdf.php
 * 新东方微社区菜单设置
 * Create By Deepseath
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
		'name' => '订阅', 'sub_button' => array(
			array(
				'type' => 'view', 'name' => '订阅中心',
				'url' => '{domain_url}/forum/forum.php?pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '我的订阅',
				'url' => '{domain_url}/forum/home.php?mod=space&do=favorite&type=forum&pluginid={pluginid}',
			)
		)
	),
	array(
		'name' => '个人中心', 'sub_button' => array(
			array(
				'type' => 'view', 'name' => '签到',
				'url' => '{domain_url}/forum/?pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '消息中心',
				'url' => '{domain_url}/forum/home.php?mod=space&do=pm&mobile=2&pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '我的账号',
				'url' => '{domain_url}/forum/home.php?mod=space&do=profile&mycenter=1&mobile=2&pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '我的帖子',
				'url' => '{domain_url}/forum/?pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '发表帖子',
				'url' => '{domain_url}/forum/forum.php?mod=post&action=newthread&mobile=2&pluginid={pluginid}',
			)
		)
	),
	array(
		'name' => '排行榜', 'sub_button' => array(
			array(
				'type' => 'view', 'name' => '订阅排行榜',
				'url' => '{domain_url}/forum/misc.php?mod=ranklist&type=activity&view=favtimes&orderby=all&mobile=2&pluginid={pluginid}',
			),
			array(
				'type' => 'view', 'name' => '阅读排行榜',
				'url' => '{domain_url}/forum/misc.php?mod=ranklist&type=thread&view=views&orderby=all&mobile=2&pluginid={pluginid}',
			)
		)
	)
);
