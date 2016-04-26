<?php
/**
 * 后台路由配置
 *
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
/** 系统默认路由 */
$conf['rules']['default'] = array(
	'module' => 'admincp',
	'controller' => 'index',
	'action' => 'home',
	'allow_modules' => array('admincp') /** 只允许访问这些modules */
);


/** 后台首页(登录后) */
$conf['rules'][] = array(
	'/admincp',
	array(
		'module' => 'admincp', 'controller' => 'setting', 'action' => 'application_list',
	)
);

/** 登录后台 */
$conf['rules'][] = array(
	'/admincp/login',
	array(
		'module' => 'admincp', 'controller' => 'adminer', 'action' => 'login',
	)
);

/** 退出后台登录 */
$conf['rules'][] = array(
	'/admincp/logout/:formhash',
	array(
		'module' => 'admincp', 'controller' => 'adminer', 'action' => 'logout',
	)
);

/** 后台登录密码重置 */
$conf['rules'][] = array(
	'admincp/pwd',
	array(
		'module' => 'admincp', 'controller' => 'adminer', 'action' => 'pwd'
	)
);

/** 手机短信发送 */
$conf['rules'][] = array(
	'admincp/sms',
	array('module' => 'admincp', 'controller' => 'adminer', 'action' => 'sms')
);

/** 后台公共组件 */
//后台编辑器上传业务处理
$conf['rules'][] = array(
	'/admincp/ueditor',
	array(
		'module' => 'admincp', 'controller' => 'misc', 'action' => 'ueditor',
	)
);
// 后台附件浏览接口
$conf['rules'][] = array(
	'/admincp/attachment',
	array(
		'module' => 'admincp', 'controller' => 'misc', 'action' => 'attachment',
	)
);
/****/

/** 人员管理 */
$conf['rules'][] = array(
	'/admincp/manage',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'member_list',
	)
);

/** 部门管理 */
// 默认
$conf['rules'][] = array(
	'/admincp/manage/department',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'department_list',
	)
);
// 部门列表
$conf['rules'][] = array(
	'/admincp/manage/department/list',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'department_list',
	)
);
// 部门管理/添加部门
$conf['rules'][] = array(
	'/admincp/manage/department/add',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'department_add',
	)
);
// 修改部门
$conf['rules'][] = array(
	'/admincp/manage/department/edit',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'department_edit',
	)
);
// 删除部门
$conf['rules'][] = array(
	'/admincp/manage/department/delete',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'department_delete',
	)
);
/****/


/** 职务管理 */
// 默认
$conf['rules'][] = array(
	'/admincp/manage/job',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'job_list',
	)
);
// 职务管理/职务列表
$conf['rules'][] = array(
	'/admincp/manage/job/list',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'job_list',
	)
);
// 添加职务
$conf['rules'][] = array(
	'/admincp/manage/job/add',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'job_add',
	)
);
// 修改删除职务
$conf['rules'][] = array(
	'/admincp/manage/job/modify',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'job_modify',
	)
);
/****/

/** 员工管理 */
// 默认
$conf['rules'][] = array(
	'/admincp/manage/member',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'member_list',
	)
);
// 员工列表
$conf['rules'][] = array(
	'/admincp/manage/member/list',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'member_list',
	)
);
// 添加员工
$conf['rules'][] = array(
	'/admincp/manage/member/add',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'member_add',
	)
);
// 编辑员工
$conf['rules'][] = array(
	'/admincp/manage/member/edit',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'member_edit',
	)
);
// 删除员工
$conf['rules'][] = array(
	'/admincp/manage/member/delete',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'member_delete',
	)
);
// 搜索员工
$conf['rules'][] = array(
	'/admincp/manage/member/search',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'member_search',
	)
);
// 导入员工
$conf['rules'][] = array(
	'/admincp/manage/member/import',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'member_import',
	)
);
// 导出员工
$conf['rules'][] = array(
	'/admincp/manage/member/dump',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'member_dump',
	)
);
// 微信企业同步员工
$conf['rules'][] = array(
	'/admincp/manage/member/impqywx',
	array(
		'module' => 'admincp', 'controller' => 'manage', 'action' => 'member_impqywx'
	)
);
/****/


/** 应用中心 */
// 默认
$conf['rules'][] = array(
	'/admincp/setting',
	array(
		'module' => 'admincp', 'controller' => 'setting', 'action' => 'application_list',
	)
);
// 应用中心
$conf['rules'][] = array(
	'/admincp/setting/application',
	array(
		'module' => 'admincp', 'controller' => 'setting', 'action' => 'application_list',
	)
);
// 应用列表
$conf['rules'][] = array(
	'/admincp/setting/application/list',
	array(
		'module' => 'admincp', 'controller' => 'setting', 'action' => 'application_list',
	)
);
// 开启关闭应用
$conf['rules'][] = array(
	'/admincp/setting/application/edit',
	array(
		'module' => 'admincp', 'controller' => 'setting', 'action' => 'application_edit',
	)
);
// 删除应用
$conf['rules'][] = array(
	'/admincp/setting/application/delete',
	array(
		'module' => 'admincp', 'controller' => 'setting', 'action' => 'application_delete',
	)
);
/****/

/** 配置服务类型 */
$conf['rules'][] = array(
	'/admincp/servicetype/',
	array(
		'module' => 'admincp', 'controller' => 'setting', 'action' => 'servicetype_modify',
	)
);
$conf['rules'][] = array(
	'/admincp/setting/servicetype',
	array(
		'module' => 'admincp', 'controller' => 'setting', 'action' => 'servicetype_modify',
	)
);
$conf['rules'][] = array(
	'/admincp/setting/servicetype/modify',
	array(
		'module' => 'admincp', 'controller' => 'setting', 'action' => 'servicetype_modify',
	)
);
/****/


/** 系统设置 */
// 默认后台管理员列表
$conf['rules'][] = array(
	'/admincp/system',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'adminer_list',
	)
);


/** 系统环境设置 */
$conf['rules'][] = array(
	'/admincp/system/setting',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'setting_modify',
	)
);
// 更改设置
$conf['rules'][] = array(
	'/admincp/system/setting/modify',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'setting_modify',
	)
);
/****/

/** 管理员个人资料 */
// 默认修改密码
$conf['rules'][] = array(
	'/admincp/system/profile/',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'profile_pwd'
	)
);
// 修改密码
$conf['rules'][] = array(
	'/admincp/system/profile/pwd/',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'profile_pwd'
	)
);
/****/

/** 更新缓存 */
$conf['rules'][] = array(
	'/admincp/system/cache',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'cache_refresh',
	)
);
// 更新缓存
$conf['rules'][] = array(
	'/admincp/system/cache/refresh',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'cache_refresh',
	)
);
/****/

/** 后台管理员 */
$conf['rules'][] = array(
	'/admincp/system/adminer',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'adminer_list',
	)
);
// 管理员列表
$conf['rules'][] = array(
	'/admincp/system/adminer/list',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'adminer_list',
	)
);
// 添加管理员
$conf['rules'][] = array(
	'/admincp/system/adminer/add',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'adminer_add',
	)
);
// 删除管理员
$conf['rules'][] = array(
	'/admincp/system/adminer/delete',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'adminer_delete',
	)
);
// 编辑管理员
$conf['rules'][] = array(
	'/admincp/system/adminer/edit',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'adminer_edit',
	)
);
/****/

/** 后台管理组 */
$conf['rules'][] = array(
	'/admincp/system/adminergroup',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'adminergroup_list',
	)
);
// 管理组列表
$conf['rules'][] = array(
	'/admincp/system/adminergroup/list',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'adminergroup_list',
	)
);
// 添加管理组
$conf['rules'][] = array(
	'/admincp/system/adminergroup/add',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'adminergroup_add',
	)
);
// 编辑管理组
$conf['rules'][] = array(
	'/admincp/system/adminergroup/edit',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'adminergroup_edit',
	)
);
// 删除管理组
$conf['rules'][] = array(
	'/admincp/system/adminergroup/delete',
	array(
		'module' => 'admincp', 'controller' => 'system', 'action' => 'adminergroup_delete',
	)
);
/****/


/** 默认OA应用 */
$conf['rules'][] = array(
	'/admincp/office',
	array(
		'module' => 'admincp', 'controller' => 'office', 'action' => 'default',
	)
);


/** 默认个人工具应用 */
$conf['rules'][] = array(
	'/admincp/tool',
	array(
		'module' => 'admincp', 'controller' => 'tool', 'action' => 'default',
	)
);
/****/
