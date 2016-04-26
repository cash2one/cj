<?php

/**
 * 路由配置
 *
 * $Id$
 */
// 默认路由设置
// + controller
// + action
// + module

/**
 * 自定义路由规则
 *
 * 每个路由为一个数组组成，而每个数组由3个值组成（最后一个可以省略）
 *  + 第一个值表式需要匹配的url，其中，变量以":"开头，可以使用通配符'*'
 *  + 第二个值为一个数组，用来指定module, controller, action。其中，module可以不指定
 *  + 第三个值为一个数组，表式每个变量应该符合的规则，如果不指定，则相应的变量允许任何规则
 *
 * 例如，将 http://domain/view/11 路由到 http://domain/user/entry/view/eId/11 时：
 *
 * <code>
 * $conf['rules'][] = array(
 *	 'view/:eId',
 *	 array(
 *		 'module' => 'user',
 *		 'controller' => 'entry',
 *		 'action' => 'view'
 *	 ),
 *	 array(
 *		 'eId' => '\d+'
 *	 )
 * );
 * </code>
 *
 */
$conf['rules']['default'] = array(
	'module' => 'main',
	'controller' => 'index',
	'action' => 'home',
	'allow_modules' => array('main') /** 只允许访问这些modules */
);

/** 首页 */
$conf['rules'][] = array(
	'/',
	array('module' => 'main', 'controller' => 'index', 'action' => 'home')
);

/** 招聘 */
$conf['rules'][] = array(
	'/join',
	array('module' => 'main', 'controller' => 'index', 'action' => 'join')
);

/** 关于我们 */
$conf['rules'][] = array(
	'/aboutus',
	array('module' => 'main', 'controller' => 'index', 'action' => 'aboutus')
);

/** 联系我们 */
$conf['rules'][] = array(
	'/contact',
	array('module' => 'main', 'controller' => 'index', 'action' => 'contact')
);

/** 常见问题 */
$conf['rules'][] = array(
	'/faq',
	array('module' => 'main', 'controller' => 'index', 'action' => 'faq')
);

/** 使用条款 */
$conf['rules'][] = array(
	'/rules',
	array('module' => 'main', 'controller' => 'index', 'action' => 'rules')
);

/** 安全 */
$conf['rules'][] = array(
	'/safe',
	array('module' => 'main', 'controller' => 'index', 'action' => 'safe')
);

/** 下载 */
$conf['rules'][] = array(
	'/download',
	array('module' => 'main', 'controller' => 'index', 'action' => 'download')
);

/** 登陆 */
$conf['rules'][] = array(
	'/login',
	array('module' => 'main', 'controller' => 'member', 'action' => 'login')
);

/** 退出 */
$conf['rules'][] = array(
	'/logout/:formhash',
	array('module' => 'main', 'controller' => 'member', 'action' => 'logout')
);

/** 注册 */
$conf['rules'][] = array(
	'/register',
	array('module' => 'main', 'controller' => 'member', 'action' => 'register')
);

/** ~~~~~ product begin ~~~ */
/** product/产品主页 */
$conf['rules'][] = array(
	'/product',
	array('module' => 'main', 'controller' => 'product', 'action' => 'index')
);

/** product/产品信息 */
$conf['rules'][] = array(
	'/product/view/:pluginname',
	array('module' => 'main', 'controller' => 'product', 'action' => 'view')
);
/** ~~~~~ product end ~~~ */

/** 找回密码 */
$conf['rules'][] = array(
	'/pwd/',
	array('module' => 'main', 'controller' => 'pwd', 'action' => 'get')
);
/** 重置密码 */
$conf['rules'][] = array(
	'/pwdreset/',
	array('module' => 'main', 'controller' => 'pwd', 'action' => 'reset')
);

/** ~~~~~ php 业务操作(一般用于解决跨域访问uc接口问题、转发) ~~~ */
// 企业注册
$conf['rules'][] = array(
	'/operation/register',
	array('module' => 'main', 'controller' => 'operation', 'action' => 'register')
);
// 获取验证码
$conf['rules'][] = array(
	'/operation/mobileverify',
	array('module' => 'main', 'controller' => 'operation', 'action' => 'mobileverify')
);
// 重置密码处理
$conf['rules'][] = array(
	'/operation/pwd',
	array('module' => 'main', 'controller' => 'operation', 'action' => 'pwd')
);
// 重置密码（邮箱）
$conf['rules'][] = array(
	'/operation/pwdreset',
	array('module' => 'main', 'controller' => 'operation', 'action' => 'pwdreset')
);
/** ~~~~~  结束 php 业务操作 ~~~*/
