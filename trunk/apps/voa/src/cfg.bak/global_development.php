<?php
/**
 * 缓存配置文件
 *
 * $Author$
 * $Id$
 */

/** 生成环境, 项目全局配置 */
$conf = array(
	'page_break' => "\r\np\n\r",
	'dateformat' => 'Y-m-d',
	'timeformat' => 'H:i',
	'timeoffset' => '8',
	'language' => 'zh_cn',
	'wxwall_path' => 'wall',
	'auth_key' => 'what\'s auth key in wbs?', /** 默认密钥 */
	'oa_top_domain' => 'vchangyi.com', /** oa 站点默认顶级域名 */
	'customer_groupid' => 9,

	// 前台静态目录定义
	'staticdir' => '/misc/',/** 静态目录位置 */
	'imgdir' => '/misc/images/', /** 默认图标路径 */
	'cssdir' => '/misc/styles', /** 样式文件路径 */
	'scriptdir' => '/misc/scripts/', /** js 文件路径 */

	/** 以下设置一般用于调试，生产环境请根据实际需要调整 */
	'use_qywx_api' => 'self',// 是否使用企业微信接口 设置为：qywx=使用企业微信接口（目前未开通）、cyadmin=使用畅移后台开通、self=自主开通
    'use_qywx_menu_api' => true,// 是否使用企业微信接口设置应用的菜单。false不使用接口，true使用
    'status_change_expire' => 86400,// 两次操作同一应用状态最小间隔时间。单位：秒。如果用于非生产环境可适当调整，如果用于开发环境，可设置小一些，比如：1

	'uc_url' => 'http://uc.dev.vchangyi.com/',//UC 根目录Url
	'cyadmin_url' => 'http://cyadmin.local.vchangyi.net/',//后台 根目录
	'main_url' => 'http://www.local.vchangyi.net/',// 主站根目录url

	'oa_http_scheme' => 'http://',// OA企业站HTTP协议，https:// 或 http://

	'smscode_send_frequency' => 1,// 同一手机号 或 同一IP两次发送手机短信验证码的间隔时间。单位：秒。必须大于10
	'smscode_send_expire' => 1800,// 验证码有效期
	'smscode_send' => false,// 是否发送验证码短信，调试时可关闭，避免频繁发送

	'api_auth_ignore' => false,// 是否忽略api接口的身份验证，设置为：false则不验证api接口访问者的身份，true则会验证
	'app_news_h5' => '/h5/index.html#/app/page/news/news-list',//新闻公告首页地址
);
