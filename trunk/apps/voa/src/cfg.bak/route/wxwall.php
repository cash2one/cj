<?php
/**
 * wall.php
 * 微信墙前端路由配置
 * Create By Deepseath
 * $Author$
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
		'module' => 'wxwall',
		'controller' => 'index',
		'action' => 'home',
		'allow_modules' => array('wxwall') /** 只允许访问这些modules */
);

/** 首页 */
$conf['rules'][] = array(
		'/wall/',
		array(
				'module' => 'wxwall',
				'controller' => 'admincp',
				'action' => 'homepage'
		)
);
$conf['rules'][] = array(
		'/wall/homepage',
		array(
				'module' => 'wxwall',
				'controller' => 'admincp',
				'action' => 'homepage'
		)
);

/** 登录 */
$conf['rules'][] = array(
		'/wall/login',
		array(
				'module' => 'wxwall',
				'controller' => 'admincp',
				'action' => 'login'
		)
);

/** 退出 */
$conf['rules'][] = array(
		'/wall/logout/:formhash',
		array(
				'module' => 'wxwall',
				'controller' => 'admincp',
				'action' => 'logout'
		)
);

/** 微信墙设置 */
$conf['rules'][] = array(
		'/wall/setting',
		array(
				'module' => 'wxwall',
				'controller' => 'admincp',
				'action' => 'setting_form'
		)
);
/** 微信墙设置 */
$conf['rules'][] = array(
		'/wall/setting/form',
		array(
				'module' => 'wxwall',
				'controller' => 'admincp',
				'action' => 'setting_form'
		)
);

/** 微信墙内容审核 - 列表（默认） */
$conf['rules'][] = array(
		'/wall/verify',
		array(
				'module' => 'wxwall',
				'controller' => 'admincp',
				'action' => 'verify_list'
		),
);

/** 微信墙内容审核 - 列表 */
$conf['rules'][] = array(
		'/wall/verify/list',
		array(
				'module' => 'wxwall',
				'controller' => 'admincp',
				'action' => 'verify_list'
		),
);

/** 微信墙内容审核 - 删除、改变状态 */
$conf['rules'][] = array(
		'/wall/verify/update',
		array(
				'module' => 'wxwall',
				'controller' => 'admincp',
				'action' => 'verify_update'
		),
);

/** 微信墙展示 */
$conf['rules'][] = array(
		'/wall/:ww_id',
		array(
				'module' => 'wxwall',
				'controller' => 'frontend',
				'action' => 'homepage'
		),
		array(
				'ww_id' => '\d+'
		)
);

/** 微信墙前端，获取最新的微信墙内容 */
$conf['rules'][] = array(
		'/wall/getnewlist',
		array(
				'module' => 'wxwall',
				'controller' => 'frontend',
				'action' => 'getnewlist'
		)
);

/** 微信墙前端，更新二维码 */
$conf['rules'][] = array(
		'/wall/updateqrcode',
		array(
				'module' => 'wxwall',
				'controller' => 'frontend',
				'action' => 'updateqrcode'
		)
);
