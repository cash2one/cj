<?php
/**
 * 主站后台路由配置
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
/** 系统默认路由 */
$conf['rules']['default'] = array(
		'module' => 'cyadmin',
		'controller' => 'index',
		'action' => 'home',
		'allow_modules' => array('cyadmin') /** 只允许访问这些modules */
);

/** 后台首页(登录后) */
$conf['rules'][] = array(
	'/',
	array(
		'module' => 'cyadmin',
		'controller' => 'index',
		'action' => 'home',
	)
);

/** 登录后台 */
$conf['rules'][] = array(
	'/login',
	array(
		'module' => 'cyadmin',
		'controller' => 'auth',
		'action' => 'login',
	)
);

/** 退出登录 */
$conf['rules'][] = array(
	'/logout/:formhash',
	array(
		'module' => 'cyadmin',
		'controller' => 'auth',
		'action' => 'logout',
	)
);

/** 后台编辑器上传业务处理 */
$conf['rules'][] = array(
	'/ueditor',
	array(
		'module' => 'cyadmin',
		'controller' => 'misc',
		'action' => 'ueditor',
	)
);

/** 后台附件浏览接口 */
$conf['rules'][] = array(
	'/attachment',
	array(
		'module' => 'cyadmin',
		'controller' => 'misc',
		'action' => 'attachment',
	)
);

/** /后台管理 */
$conf['rules'][] = array(
	'/manage',
	array(
		'module' => 'cyadmin',
		'controller' => 'manage',
		'action' => 'adminer_list',
	)
);

/** /后台管理/后台管理员 */
$conf['rules'][] = array(
	'/manage/adminer',
	array(
		'module' => 'cyadmin',
		'controller' => 'manage',
		'action' => 'adminer_list',
	)
);

/** /后台管理/后台管理员/管理员列表 */
$conf['rules'][] = array(
	'/manage/adminer/list',
	array(
		'module' => 'cyadmin',
		'controller' => 'manage',
		'action' => 'adminer_list',
	)
);

/** /后台管理/后台管理员/添加管理员 */
$conf['rules'][] = array(
	'/manage/adminer/add',
	array(
		'module' => 'cyadmin',
		'controller' => 'manage',
		'action' => 'adminer_add',
	)
);

/** /后台管理/后台管理员/删除管理员 */
$conf['rules'][] = array(
	'/manage/adminer/delete',
	array(
		'module' => 'cyadmin',
		'controller' => 'manage',
		'action' => 'adminer_delete',
	)
);

/** /后台管理/后台管理员/编辑管理员 */
$conf['rules'][] = array(
	'/manage/adminer/edit',
	array(
		'module' => 'cyadmin',
		'controller' => 'manage',
		'action' => 'adminer_edit',
	)
);

/** /后台管理/后台管理组/ */
$conf['rules'][] = array(
	'/manage/adminergroup',
	array(
		'module' => 'cyadmin',
		'controller' => 'manage',
		'action' => 'adminergroup_list',
	)
);

/** /后台管理/后台管理组/管理组列表 */
$conf['rules'][] = array(
	'/manage/adminergroup/list',
	array(
		'module' => 'cyadmin',
		'controller' => 'manage',
		'action' => 'adminergroup_list',
	)
);

/** /后台管理/后台管理组/添加管理组 */
$conf['rules'][] = array(
	'/manage/adminergroup/add',
	array(
		'module' => 'cyadmin',
		'controller' => 'manage',
		'action' => 'adminergroup_add',
	)
);

/** /后台管理/后台管理组/编辑管理组 */
$conf['rules'][] = array(
	'/manage/adminergroup/edit',
	array(
		'module' => 'cyadmin',
		'controller' => 'manage',
		'action' => 'adminergroup_edit',
	)
);

/** /后台管理/后台管理组/删除管理组 */
$conf['rules'][] = array(
	'/manage/adminergroup/delete',
	array(
		'module' => 'cyadmin',
		'controller' => 'manage',
		'action' => 'adminergroup_delete',
	)
);

/** /系统设置 */
$conf['rules'][] = array(
	'/setting',
	array(
		'module' => 'cyadmin',
		'controller' => 'setting',
		'action' => 'common_modify',
	)
);

/** /系统设置/系统环境设置 */
$conf['rules'][] = array(
	'/setting/common',
	array(
		'module' => 'cyadmin',
		'controller' => 'setting',
		'action' => 'common_modify',
	)
);

/** /系统设置/系统环境设置/更改设置 */
$conf['rules'][] = array(
	'/setting/common/modify',
	array(
		'module' => 'cyadmin',
		'controller' => 'setting',
		'action' => 'common_modify',
	)
);

/** /系统设置/更新缓存 */
$conf['rules'][] = array(
	'/setting/cache',
	array(
		'module' => 'cyadmin',
		'controller' => 'setting',
		'action' => 'cache_refresh',
	)
);

/** /系统设置/更新缓存/更新 */
$conf['rules'][] = array(
	'/setting/cache/refresh',
	array(
		'module' => 'cyadmin',
		'controller' => 'setting',
		'action' => 'cache_refresh',
	)
);


/** 代理加盟 */
$conf['rules'][] = array(
	'/enterprise/agent',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'agent_list',
	)
);
$conf['rules'][] = array(
	'/enterprise/agent/list',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'agent_list',
	)
);
$conf['rules'][] = array(
	'/enterprise/agent/view',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'agent_view',
	)
);
$conf['rules'][] = array(
	'/enterprise/agent/view/:id',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'agent_view',
	),
	array(
		'aid' => '\d+'
	)
);
$conf['rules'][] = array(
	'/enterprise/agent/delete',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'agent_delete',
	)
);

/** 代理加盟 */
$conf['rules'][] = array(
	'/enterprise/agent',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'agent_list',
	)
);
$conf['rules'][] = array(
	'/enterprise/agent/list',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'agent_list',
	)
);
$conf['rules'][] = array(
	'/enterprise/agent/view',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'agent_view',
	)
);
$conf['rules'][] = array(
	'/enterprise/agent/view/:id',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'agent_view',
	),
	array(
		'aid' => '\d+'
	)
);
$conf['rules'][] = array(
	'/enterprise/agent/delete',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'agent_delete',
	)
);

/** / */
$conf['rules'][] = array(
	'/enterprise',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'company_list',
	)
);
/** / */
$conf['rules'][] = array(
	'/enterprise/company',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'company_list',
	)
);
/** / */
$conf['rules'][] = array(
	'/enterprise/company/list',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'company_list',
	)
);
/** / */
$conf['rules'][] = array(
	'/enterprise/company/export',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'company_export',
	)
);
/** / */
$conf['rules'][] = array(
	'/enterprise/company/list/:id',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'company_list',
	),
	array(
		'id' => '\d+'
	)
);
/** / */
$conf['rules'][] = array(
	'/enterprise/companyon',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'companyon_list',
	)
);
/** / */
$conf['rules'][] = array(
	'/enterprise/companyon/list',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'companyon_list',
	)
);
/** / */
$conf['rules'][] = array(
	'/enterprise/companyon/list/:id',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'companyon_list',
	),
	array(
		'id' => '\d+'
	)
);
/** / */
$conf['rules'][] = array(
	'/enterprise/company/edit',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'company_edit',
	)
);
/** / */
$conf['rules'][] = array(
	'/enterprise/recbill',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'recbill_edit',
	)
);
/** / */
$conf['rules'][] = array(
	'/enterprise/recbill/edit',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'recbill_edit',
	)
);
/** / */
$conf['rules'][] = array(
	'/enterprise/recbill/edit/:id',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'recbill_edit',
	),
	array(
		'id' => '\d+'
	)
);
/** / */
$conf['rules'][] = array(
	'/enterprise/reccard',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'reccard_edit',
	)
);
/** / */
$conf['rules'][] = array(
	'/enterprise/reccard/edit',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'reccard_edit',
	)
);
$conf['rules'][] = array(
		'/enterprise/sms',
		array(
				'module' => 'cyadmin',
				'controller' => 'enterprise',
				'action' => 'sms_list',
		)
);
/** / */
$conf['rules'][] = array(
		'/enterprise/sms/list',
		array(
				'module' => 'cyadmin',
				'controller' => 'enterprise',
				'action' => 'sms_list',
		)
);

/** 账户列表 */
$conf['rules'][] = array(
	'/enterprise/account',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'account_list',
	)
);
/** 账户列表 */
$conf['rules'][] = array(
	'/enterprise/account/list',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'account_list',
	)
);
/** 账户详情 */
$conf['rules'][] = array(
	'/enterprise/account/view',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'account_view',
	)
);
/** 缴费设置 */
$conf['rules'][] = array(
	'/enterprise/account/pay',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'account_pay',
	)
);


/** 附件上传 */
$conf['rules'][] = array(
	'/attachment/upload',
	array('module' => 'cyadmin', 'controller' => 'attachment', 'action' => 'upload')
);
/**读取附件*/
$conf['rules'][] = array(
	'/attachment/read/:atid',
	array('module' => 'cyadmin', 'controller' => 'attachment', 'action' => 'read')
);
/** 内容管理 */
$conf['rules'][] = array(
	'/content/',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'article_list')
);
/** 文章列表 */
$conf['rules'][] = array(
	'/content/article',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'article_list')
);

$conf['rules'][] = array(
	'/content/article/list',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'article_list')
);
/** 添加文章 */
$conf['rules'][] = array(
	'/content/article/add',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'article_add')
);
/** 添加文章 */
$conf['rules'][] = array(
	'/content/article/insert',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'article_insert')
);
/** 编辑文章 */
$conf['rules'][] = array(
	'/content/article/edit',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'article_edit')
);
/** 编辑文章 */
$conf['rules'][] = array(
	'/content/article/update',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'article_update')
);
/** 删除文章 */
$conf['rules'][] = array(
	'/content/article/delete',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'article_delete')
);
/** 文章详情 */
$conf['rules'][] = array(
	'/content/article/view/',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'article_view')
);

/** 人才招聘列表 */
$conf['rules'][] = array(
	'/content/join/',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'join_list')
);
/** 人才招聘列表 */
$conf['rules'][] = array(
	'/content/join/list',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'join_list')
);

/** 新增招聘 */
$conf['rules'][] = array(
	'/content/join/add',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'join_add')
);
/** 变更招聘 */
$conf['rules'][] = array(
	'/content/join/edit',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'join_edit')
);
/** 变更招聘 */
$conf['rules'][] = array(
	'/content/join/new',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'join_new')
);
/** 删除招聘 */
$conf['rules'][] = array(
	'/content/join/delete',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'join_delete')
);
/** 招聘详情 */
$conf['rules'][] = array(
	'/content/join/view',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'join_view')
);

/** 友情链接列表 */
$conf['rules'][] = array(
	'/content/link/',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'link_list')
);

/** 友情链接列表 */
$conf['rules'][] = array(
	'/content/link/list',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'link_list')
);

/** 新增链接 */
$conf['rules'][] = array(
	'/content/link/add',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'link_add')
);

/** 编辑链接 */
$conf['rules'][] = array(
	'/content/link/edit',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'link_edit')
);

/** 变更链接数据 */
$conf['rules'][] = array(
	'/content/link/new',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'link_new')
);

/** 删除链接 */
$conf['rules'][] = array(
	'/content/link/delete',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'link_delete')
);

/** 链接详情 */
$conf['rules'][] = array(
	'/content/link/view',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'link_view')
);

/** 线下培训 */
$conf['rules'][] = array(
	'/content/train/',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'train_list')
);

/** 线下培训 */
$conf['rules'][] = array(
	'/content/train/list',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'train_list')
);

/** 新增培训 */
$conf['rules'][] = array(
	'/content/train/add',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'train_add')
);

/** 编辑培训 */
$conf['rules'][] = array(
	'/content/train/edit',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'train_edit')
);
/** 变更培训 */
$conf['rules'][] = array(
	'/content/train/new',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'train_new')
);

/** 培训详情 */
$conf['rules'][] = array(
	'/content/train/view',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'train_view')
);

/** 删除培训 */
$conf['rules'][] = array(
	'/content/train/delete',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'train_delete')
);

/** 报名设置 */
$conf['rules'][] = array(
	'/content/train/setting',
	array('module' => 'cyadmin', 'controller' => 'content', 'action' => 'train_setting')
);

/** 账户列表 */
$conf['rules'][] = array(
	'/enterprise/account',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'account_list',
	)
);
/** 账户列表 */
$conf['rules'][] = array(
	'/enterprise/account/list',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'account_list',
	)
);
/** 账户详情 */
$conf['rules'][] = array(
	'/enterprise/account/view',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'account_view',
	)
);
/** 缴费设置 */
$conf['rules'][] = array(
	'/enterprise/account/pay',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'account_pay',
	)
);
/** 账户删除 */
$conf['rules'][] = array(
	'/enterprise/account/delete',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'account_delete',
	)
);
/** 账户编辑 */
$conf['rules'][] = array(
	'/enterprise/account/edit',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'account_edit',
	)
);
/** 添加账户 */
$conf['rules'][] = array(
	'/enterprise/account/add',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'account_add',
	)
);
/** 发送消息 */
$conf['rules'][] = array(
	'/enterprise/company/message',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'company_message',
	)
);
/** 消息管理 */
$conf['rules'][] = array(
	'/enterprise/news',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'news_list',
	)
);
/** 发消息 */
$conf['rules'][] = array(
	'/enterprise/news/add',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'news_add',
	)
);
/** 消息 列表*/
$conf['rules'][] = array(
	'/enterprise/news/list',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'news_list',
	)
);
/** 消息删除*/
$conf['rules'][] = array(
	'/enterprise/news/delete',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'news_delete',
	)
);
/** 消息详情*/
$conf['rules'][] = array(
	'/enterprise/news/view',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'news_view',
	)
);
/** 数据维护 */
$conf['rules'][] = array(
	'/data/',
	array('module' => 'cyadmin', 'controller' => 'data', 'action' => 'template_list')
);
/** 模板管理 */
$conf['rules'][] = array(
	'/data/template/',
	array('module' => 'cyadmin', 'controller' => 'data', 'action' => 'template_list')
);
/** 模板列表 */
$conf['rules'][] = array(
	'/data/template/list',
	array('module' => 'cyadmin', 'controller' => 'data', 'action' => 'template_list')
);
/** 模板详情 */
$conf['rules'][] = array(
	'/data/template/view',
	array('module' => 'cyadmin', 'controller' => 'data', 'action' => 'template_view')
);
/** 模板删除 */
$conf['rules'][] = array(
	'/data/template/delete',
	array('module' => 'cyadmin', 'controller' => 'data', 'action' => 'template_delete')
);
/** 模板添加 */
$conf['rules'][] = array(
	'/data/template/add',
	array('module' => 'cyadmin', 'controller' => 'data', 'action' => 'template_add')
);

/** 应用设置*/
$conf['rules'][] = array(
	'/enterprise/appset',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'appset_edit',
	)
);

/** 发送消息*/
$conf['rules'][] = array(
	'/enterprise/appset/insert',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'appset_insert',
	)
);



/** 用户管理详情 */
$conf['rules'][] = array(
	'/enterprise/company/view',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'company_view',
	)
);
/** 用户管理付费设置 */
$conf['rules'][] = array(
	'/api/company/paysetting',
	array(
		'module' => 'cyadmin',
		'controller' => 'api',
		'action' => 'company_paysetting',
	)
);
/** 用户管理销售设置 */
$conf['rules'][] = array(
	'/api/company/salessetting',
	array(
		'module' => 'cyadmin',
		'controller' => 'api',
		'action' => 'company_salessetting',
	)
);
/** 消息入库接口 */
$conf['rules'][] = array(
	'/api/company/message',
	array(
		'module' => 'cyadmin',
		'controller' => 'api',
		'action' => 'company_message',
	)
);
/** 企业使用信息查询接口 */
$conf['rules'][] = array(
	'/api/company/isoverdue',
	array(
		'module' => 'cyadmin',
		'controller' => 'api',
		'action' => 'company_isoverdue',
	)
);

/** api企业信息查询接口 */
$conf['rules'][] = array(
	'/api/message/profile',
	array(
		'module' => 'cyadmin',
		'controller' => 'api',
		'action' => 'message_profile',
	)
);

/** 试用延期接口 */
$conf['rules'][] = array(
	'/api/company/extended',
	array(
		'module' => 'cyadmin',
		'controller' => 'api',
		'action' => 'company_extended',
	)
);
/** 增加客户接口 */
$conf['rules'][] = array(
	'api/company/add',
	array(
		'module' => 'cyadmin',
		'controller' => 'api',
		'action' => 'company_add',
	)
);
/** 更改客户状态 */
$conf['rules'][] = array(
	'api/company/change',
	array(
		'module' => 'cyadmin',
		'controller' => 'api',
		'action' => 'company_change',
	)
);
/** 权限管理 */
$conf['rules'][] = array(
	'/enterprise/permission',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'permission_add',
	)
);
/** 添加管理员 */
$conf['rules'][] = array(
	'/enterprise/permission/add',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'permission_add',
	)
);
/** 停止/开启套件 */
$conf['rules'][] = array(
	'api/company/stop',
	array(
		'module' => 'cyadmin',
		'controller' => 'api',
		'action' => 'company_stop',
	)
);
/** 更改负责人 */
$conf['rules'][] = array(
	'api/company/leader',
	array(
		'module' => 'cyadmin',
		'controller' => 'api',
		'action' => 'company_leader',
	)
);



// 管理员消息
$conf['rules'][] = array(
	'/enterprise/overdue',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'overdue_list',
	)
);

// 已读
$conf['rules'][] = array(
	'/enterprise/overdue/onread',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'overdue_onread',
	)
);

// 销售变更列表
$conf['rules'][] = array(
	'/company/operationrecord',
	array(
		'module' => 'cyadmin',
		'controller' => 'company',
		'action' => 'operationrecord_list',
	)
);
// 销售变更默认列表
$conf['rules'][] = array(
	'/company',
	array(
		'module' => 'cyadmin',
		'controller' => 'company',
		'action' => 'operationrecord_list',
	)
);
/** 多个企业站添加负责人 */
$conf['rules'][] = array(
	'api/company/leaders',
	array(
		'module' => 'cyadmin',
		'controller' => 'api',
		'action' => 'company_leaders',
	)
);
/** 全局 试用期记录 导出 */
$conf['rules'][] = array(
	'/enterprise/export',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'export_trial',
	)
);
/** 批量更改负责人 */
$conf['rules'][] = array(
	'api/company/batchchange',
	array(
		'module' => 'cyadmin',
		'controller' => 'api',
		'action' => 'company_batchchange',
	)
);
/** 迁移负责人 */
$conf['rules'][] = array(
	'api/company/transfer',
	array(
		'module' => 'cyadmin',
		'controller' => 'api',
		'action' => 'company_transfer',
	)
);

/** 数据统计 */
$conf['rules'][] = array(
	'/enterprise/stat',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'stat_list',
	)
);
$conf['rules'][] = array(
	'/enterprise/stat/list',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'stat_list',
	)
);
$conf['rules'][] = array(
	'/enterprise/stat/company',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'stat_company',
	)
);
$conf['rules'][] = array(
	'/enterprise/stat/plugin',
	array(
		'module' => 'cyadmin',
		'controller' => 'enterprise',
		'action' => 'stat_plugin',
	)
);