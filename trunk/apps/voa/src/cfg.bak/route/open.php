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
	'module' => 'open',
	'controller' => 'index',
	'action' => 'home',
	'allow_modules' => array('open') /** 只允许访问这些modules */
);

/** 首页 */
$conf['rules'][] = array(
	'/',
	array(
		'module' => 'open',
		'controller' => 'index',
		'action' => 'home'
	)
);

/** 登陆 */
$conf['rules'][] = array(
	'/login',
	array(
		'module' => 'open',
		'controller' => 'member',
		'action' => 'login'
	)
);

/** 退出 */
$conf['rules'][] = array(
	'/logout/:formhash',
	array(
		'module' => 'open',
		'controller' => 'member',
		'action' => 'logout'
	)
);

/** 注册 */
$conf['rules'][] = array(
	'/register',
	array(
		'module' => 'open',
		'controller' => 'member',
		'action' => 'register'
	)
);
