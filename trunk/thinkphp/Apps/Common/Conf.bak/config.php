<?php
/**
 * config.php
 * 公共配置
 * $Author$
 */

// 用于测试服务器上临时解决前端js请求跨域问题的
if (isset($_SERVER['HTTP_REFERER'])) {
	$url_parse = @parse_url($_SERVER['HTTP_REFERER']);
	$port = '';
	if (isset($url_parse['port']) && 80 != $url_parse['port']) {
		$port = ':' . $url_parse['port'];
	}

	@header("Access-Control-Allow-Origin: " . $url_parse['scheme'] . '://' . $url_parse['host'] . $port);
}

@header("Access-Control-Allow-Credentials: true");
@header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
//@header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

return array(
	//'配置项'=>'配置值'
	'INSTALL_MODE' => 2, // 安装模式, 1: 标准安装; 2: 独立部署

	// 是否 js 登录
	'JS_LOGIN' => false,
	// H5 默认路径
	'H5_PATH' => '/h5/index.html',

	// Cookie 键值前缀(后端 Cookie)
	'COOKIE_CP_PREKEY' => 'qywx_',
	// ucenter RPC 地址
	'UCENTER_RPC_HOST' => 'http://uc.vcy.com',
	// cyadmin RPC 地址
	'CYADMIN_RPC_HOST' => 'http://cyadmin.vcy.com',

	// Dnspod 配置
	'DNSPOD' => array(
		'USER' => '', // 用户名
		'PASSWD' => '', // 密码
		'ZONEID' => '' // Zone ID
	),

	// MailCloud 配置
	'MAILCLOUD' => array(
		'ACCOUNT' => 'postmaster@single-vchangyi.sendcloud.org',
		'PASSWORD' => 'UkuZZbhoc58Tbq4U',
		'FROM' => 'service@vchangyi.com',
		'FROMNAME' => '畅移'
	),

	// 邮件模板
	'TPLS' => array(
		'REGISTER' => 'qrcode_email_verify',
		'PWDRESET' => 'password_reset',
		'REGISTER_VCHANGYI' => 'voa_register_success',
		'REGISTER_CYADMIN' => 'cyadmin_register_success',
		'INVITE_FOLLOW' => 'invite_follow'
	),
	'SUBJECT_FOR_REGISTER' => '感谢您注册畅移云工作，欢迎使用。', /** 注册开通企业号时的邮件标题 */
	'WXQY_FOLLOW_PUSH_MSG' => '温馨提示：为了更好的办公体验，畅移信息现提供个人网页版，员工可以在电脑上更方便的使用畅移应用，与微信企业号功能实现无缝连接。那么，如何登录畅移个人网页版呢？请复制下面链接到浏览器中打开：',
	'SUBJECT_FOR_INVITE_FOLLOW' => '邀请您关注微信企业号',
	'REGISTER_SUCCEED_MSG' => "感谢您注册畅移云工作！您的注册信息如下：公司名称：%s，  公司账号：%s，  手机号码：%s，  邮箱地址：%s，  企业后台地址：%s，  登录企业后台的账号即注册时的手机号，密码为注册时设置的密码。另外，为了更好的办公体验，畅移信息现提供个人网页版，员工可以在电脑上更方便的使用畅移应用，与微信企业号功能实现无缝连接。请复制右边链接到浏览器中打开：%s 如有问题，请致电 4008606961",
	'CYADMIN_REGISTER_SUCCEED_MSG' => "感谢您注册畅移云工作！您的注册信息如下：公司名称：%s，  公司账号：%s，  手机号码：%s，  邮箱地址：%s，  企业后台地址：%s，  账户初始信息请找专属客服索取。另外，为了更好的办公体验，畅移信息现提供个人网页版，员工可以在电脑上更方便的使用畅移应用，与微信企业号功能实现无缝连接。请复制右边链接到浏览器中打开：%s 如有问题，请致电 4008606961",

	// 插件ID
	'PLUGIN_ID' => 0,
	// 套件ID
	'SUITE_ID' => '',
	// 应用ID
	'AGENT_ID' => 0,

	// 微信错误号相关
	'WX_API_ERRCODE_60011' => 60011, // 没有通讯录权限的错误号
	'WX_API_ERRCODE_60111' => 60111, // 用户 userid 不存在
	'WX_API_ERRCODE_60102' => 60102, // 用户 userid 已存在

	// 开启多语言
	'LANG_SWITCH_ON' => true,
	// 自动侦测语言 开启多语言功能后有效
	'LANG_AUTO_DETECT' => true,
	// 允许切换的语言列表 用逗号分隔
	'LANG_LIST' => 'zh-cn',
	// 默认语言切换变量
	'VAR_LANGUAGE' => 'lang',

	// redis 配置
	'REDIS_HOST' => '10.66.89.179',
	'REDIS_PORT' => '6379',
	'REDIS_PWD' => '1afe02e4-a956-4cb6-9825-094dfae4b702:9iRYJ8CkH44e2em6'
);
