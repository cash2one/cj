<?php
/**
 * config.php
 * 公共配置
 * $Author$
 */

return array(
	//'配置项'=>'配置值'

	// 过期时间提前多久
	'SUITE_TOKEN_EXPIRE_AHEAD' => 300,

	// 短信每个IP每天最大值
	'SMS_IP_LIMIT_PERDAY' => 10,
	// 短信每小时最大值
	'SMS_MT_LIMIT_PERHOUR' => 3,
	// 短信每天最大值
	'SMS_MT_LIMIT_PERDAY' => 10,

	// 验证码开关
	'SMSCODE_SWITCH' => false,
	// 默认验证码消息文本
	'SMSCODE_DEFAULT_MSG' => "验证码：%seccode%，请在%expire%之内完成操作。（如非本人操作，请忽略本短信）",
	// 验证码有效期
	'SMSCODE_EXPIRE' => 1800,
	// 验证码发送的时间间隔
	'SMSCODE_FREQUENCY' => 60,
	// 验证码长度
	'SMSCODE_LENGTH' => 6,

	'SMS_TPL' => array(
		'REGISTER' => '验证码：%seccode%，请在%expire%之内完成操作。（如非本人操作，请忽略本短信）',
		'PWDRESET' => '验证码：%seccode%，请在%expire%之内完成操作。（如非本人操作，请忽略本短信）'
	),

	// cyadmin RPC 地址
	'CYADMIN_RPC_HOST' => 'http://cyadmin.vcy.com',
	// oa RPC 地址, 需要程序动态修改
	'OA_RPC_HOST' => '',

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
    'DB_NAME' => 'vucenter', // 数据库名
    'DB_USER' => 'root', // 用户名
    'DB_PWD' => '101937', // 密码
    'DB_PORT' => '3306', // 端口
	'DB_PREFIX' => 'uc_', // 数据库表前缀
	'DB_CHARSET' => 'utf8', // 数据库编码默认采用utf8
);
