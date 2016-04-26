<?php
/**
 * config.php
 * 公共配置
 * $Author$
 */

return array(
	//'配置项'=>'配置值'

	// oa RPC 地址, 需要程序动态修改
	'OA_RPC_HOST' => '',
	// ucenter RPC 地址
	'UCENTER_RPC_HOST' => 'http://uc.vcy.com',

	// 插件ID
	'PLUGIN_ID' => 0,
	// 套件ID
	'SUITE_ID' => '',
	// 应用ID
	'AGENT_ID' => 0,

	// 开启多语言
	'LANG_SWITCH_ON' => true,
	// 自动侦测语言 开启多语言功能后有效
	'LANG_AUTO_DETECT' => true,
	// 允许切换的语言列表 用逗号分隔
	'LANG_LIST' => 'zh-cn',
	// 默认语言切换变量
	'VAR_LANGUAGE' => 'lang',

	/* 数据库设置 */
	'DB_TYPE' => 'mysql', // 数据库类型
	'DB_HOST' => '127.0.0.1', // 服务器地址
	'DB_NAME' => 'vchangyi_cyadmin', // 数据库名
	'DB_USER' => 'root', // 用户名
	'DB_PWD' => '', // 密码
	'DB_PORT' => '3306', // 端口
	'DB_PREFIX' => 'cy_', // 数据库表前缀
	'DB_CHARSET' => 'utf8', // 数据库编码默认采用utf8

	// 时间以及时区
	'DATE_FORMAT' => 'Y-m-d',
	'TIME_FORMAT' => 'H:i',
	'TIME_OFFSET' => '8'
);
