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
	'module' => 'frontend',
	'controller' => 'index',
	'action' => 'home',
	'allow_modules' => array('frontend') /** 只允许访问这些modules */
);

/** 微信消息/发送 */
$conf['rules'][] = array(
	'/qywxmsg/send',
	array('module' => 'frontend', 'controller' => 'qywxmsg', 'action' => 'send')
);

/** 主页 */
$conf['rules'][] = array(
	'/',
	array('module' => 'frontend', 'controller' => 'index', 'action' => 'home')
);

/** fontend/member begin */
/** 用户注册 */
$conf['rules'][] = array(
	'/register',
	array('module' => 'frontend', 'controller' => 'member', 'action' => 'register')
);

/** 登陆 */
$conf['rules'][] = array(
	'/login',
	array('module' => 'frontend', 'controller' => 'member', 'action' => 'login')
);

/** 退出 */
$conf['rules'][] = array(
	'/logout/:formhash',
	array('module' => 'frontend', 'controller' => 'member', 'action' => 'logout')
);

/** 用户中心 */
$conf['rules'][] = array(
	'/member/center',
	array('module' => 'frontend', 'controller' => 'member', 'action' => 'center')
);

/** 用户密码相关 */
$conf['rules'][] = array(
	'/member/password',
	array('module' => 'frontend', 'controller' => 'member', 'action' => 'password')
);

/** 用户列表, 供页面调用 */
$conf['rules'][] = array(
	'/member/list',
	array('module' => 'frontend', 'controller' => 'member', 'action' => 'list')
);
/** frontend/member end */

/** frontend/addressbook end */
/** 通讯录/列表 */
$conf['rules'][] = array(
	'/addressbook',
	array('module' => 'frontend', 'controller' => 'addressbook', 'action' => 'list')
);

/** 通讯录/列表 */
$conf['rules'][] = array(
	'/addressbook/list',
	array('module' => 'frontend', 'controller' => 'addressbook', 'action' => 'list')
);

/** 查看指定用户通讯录信息 */
$conf['rules'][] = array(
	'/addressbook/show/:uid',
	array('module' => 'frontend', 'controller' => 'addressbook', 'action' => 'show')
);

/** 编辑自己通讯录信息 */
$conf['rules'][] = array(
	'/addressbook/edit',
	array('module' => 'frontend', 'controller' => 'addressbook', 'action' => 'edit')
);
/** frontend/addressbook end */

/** frontend/askfor begin */
/** 审核/流程列表 */
$conf['rules'][] = array(
	'/askfor/template',
	array('module' => 'frontend', 'controller' => 'askfor', 'action' => 'template')
);
/** 审核/新增 */
$conf['rules'][] = array(
	'/askfor/new/:aft_id',
	array('module' => 'frontend', 'controller' => 'askfor', 'action' => 'new')
);

/** 审核/列表 */
$conf['rules'][] = array(
	'/askfor/list',
	array('module' => 'frontend', 'controller' => 'askfor', 'action' => 'list')
);

/** 审核/列表 */
$conf['rules'][] = array(
	'/askfor/list/:ac',
	array('module' => 'frontend', 'controller' => 'askfor', 'action' => 'list')
);
/** 审核/列表 */
$conf['rules'][] = array(
	'/askfor/record',
	array('module' => 'frontend', 'controller' => 'askfor', 'action' => 'record')
);
/** 审核/查看 */
$conf['rules'][] = array(
	'/askfor/view/:af_id',
	array('module' => 'frontend', 'controller' => 'askfor', 'action' => 'view')
);

/** 审核/留言(评论) */
$conf['rules'][] = array(
	'/askfor/comment/:af_id',
	array('module' => 'frontend', 'controller' => 'askfor', 'action' => 'comment')
);

/** 审核/对留言的回复 */
$conf['rules'][] = array(
	'/askfor/reply/:afc_id',
	array('module' => 'frontend', 'controller' => 'askfor', 'action' => 'reply')
);

/** 审核/同意 */
$conf['rules'][] = array(
	'/askfor/approve/:af_id',
	array('module' => 'frontend', 'controller' => 'askfor', 'action' => 'verify_approve')
);

/** 审核/拒绝 */
$conf['rules'][] = array(
	'/askfor/refuse/:af_id',
	array('module' => 'frontend', 'controller' => 'askfor', 'action' => 'verify_refuse')
);

/** 审核/同意并转审批 */
$conf['rules'][] = array(
	'/askfor/transmit/:af_id',
	array('module' => 'frontend', 'controller' => 'askfor', 'action' => 'verify_transmit')
);
/** 催办审批 */
$conf['rules'][] = array(
	'/askfor/reminder/:af_id',
	array('module' => 'frontend', 'controller' => 'askfor', 'action' => 'verify_reminder')
);
/** 撤销审批 */
$conf['rules'][] = array(
	'/askfor/cancel/:af_id',
	array('module' => 'frontend', 'controller' => 'askfor', 'action' => 'verify_cancel')
);
/** frontend/askfor end */

/** frontend/askoff begin */
/** 请假/列表 */
$conf['rules'][] = array(
	'/askoff',
	array('module' => 'frontend', 'controller' => 'askoff', 'action' => 'list')
);

/** 请假/列表 */
$conf['rules'][] = array(
	'/askoff/list',
	array('module' => 'frontend', 'controller' => 'askoff', 'action' => 'list')
);

/** 请假/带审批 */
$conf['rules'][] = array(
	'/askoff/deal',
	array('module' => 'frontend', 'controller' => 'askoff', 'action' => 'deal')
);

/** 请假/新增 */
$conf['rules'][] = array(
	'/askoff/new',
	array('module' => 'frontend', 'controller' => 'askoff', 'action' => 'new')
);

/** 请假/查看 */
$conf['rules'][] = array(
	'/askoff/view/:ao_id',
	array('module' => 'frontend', 'controller' => 'askoff', 'action' => 'view')
);

/** 请假/回复(留言) */
$conf['rules'][] = array(
	'/askoff/reply/:ao_id',
	array('module' => 'frontend', 'controller' => 'askoff', 'action' => 'reply')
);

/** 请假/同意 */
$conf['rules'][] = array(
	'/askoff/approve/:ao_id',
	array('module' => 'frontend', 'controller' => 'askoff', 'action' => 'verify_approve')
);

/** 请假/拒绝 */
$conf['rules'][] = array(
	'/askoff/refuse/:ao_id',
	array('module' => 'frontend', 'controller' => 'askoff', 'action' => 'verify_refuse')
);

/** 请假/同意并转审批 */
$conf['rules'][] = array(
	'/askoff/transmit/:ao_id',
	array('module' => 'frontend', 'controller' => 'askoff', 'action' => 'verify_transmit')
);
/** frontend/askoff end */

/** frontend/meeting begin */
/** 会议/列表 */
$conf['rules'][] = array(
	'/meeting',
	array('module' => 'frontend', 'controller' => 'meeting', 'action' => 'list')
);

$conf['rules'][] = array(
	'meeting/list',
	array('module' => 'frontend', 'controller' => 'meeting', 'action' => 'list')
);

$conf['rules'][] = array(
	'/meeting/list/:ac',
	array('module' => 'frontend', 'controller' => 'meeting', 'action' => 'list')
);

/** 会议/新增 */
$conf['rules'][] = array(
	'/meeting/new',
	array('module' => 'frontend', 'controller' => 'meeting', 'action' => 'new')
);

/** 会议/展示 */
$conf['rules'][] = array(
	'/meeting/view/:mt_id',
	array('module' => 'frontend', 'controller' => 'meeting', 'action' => 'view')
);

/** 会议/确认参加 */
$conf['rules'][] = array(
	'/meeting/confirm/:mt_id',
	array('module' => 'frontend', 'controller' => 'meeting', 'action' => 'confirm')
);

/** 会议/缺席(不参加) */
$conf['rules'][] = array(
	'/meeting/absence/:mt_id',
	array('module' => 'frontend', 'controller' => 'meeting', 'action' => 'absence')
);

/** 会议/取消 */
$conf['rules'][] = array(
	'/meeting/cancel/:mt_id',
	array('module' => 'frontend', 'controller' => 'meeting', 'action' => 'cancel')
);
/** frontend/meeting end */

/** frontend/sign begin */
/** 考勤信息/主页 */
$conf['rules'][] = array(
	'/sign',
	array('module' => 'frontend', 'controller' => 'sign', 'action' => 'index')
);

/** 考勤信息/考勤列表 */
$conf['rules'][] = array(
	'/sign/list',
	array('module' => 'frontend', 'controller' => 'sign', 'action' => 'list')
);

/** 考勤信息/考勤投诉 */
$conf['rules'][] = array(
	'/sign/plead',
	array('module' => 'frontend', 'controller' => 'sign', 'action' => 'plead')
);

/** 考勤信息/位置打卡 */
$conf['rules'][] = array(
	'/sign/location',
	array('module' => 'frontend', 'controller' => 'sign', 'action' => 'location')
);

/** 考勤信息/ip打卡 */
$conf['rules'][] = array(
	'/sign/ip',
	array('module' => 'frontend', 'controller' => 'sign', 'action' => 'ip')
);

/** 考勤二维码 */
$conf['rules'][] = array(
	'/sign/qrcode',
	array('module' => 'frontend', 'controller' => 'sign', 'action' => 'qrcode')
);
/** frontend/sign end */

/** frontend/project begin */
/** 任务/列表 */
$conf['rules'][] = array(
	'/project/list',
	array('module' => 'frontend', 'controller' => 'project', 'action' => 'list')
);

$conf['rules'][] = array(
	'/project/list/:ac',
	array('module' => 'frontend', 'controller' => 'project', 'action' => 'list')
);

/** 任务/新增 */
$conf['rules'][] = array(
	'/project/new',
	array('module' => 'frontend', 'controller' => 'project', 'action' => 'new')
);

/** 任务/查看 */
$conf['rules'][] = array(
	'/project/view/:p_id',
	array('module' => 'frontend', 'controller' => 'project', 'action' => 'view')
);

/** 任务/更新进度 */
$conf['rules'][] = array(
	'/project/progress/:p_id',
	array('module' => 'frontend', 'controller' => 'project', 'action' => 'progress')
);

/** 任务/关闭 */
$conf['rules'][] = array(
	'/project/close/:p_id',
	array('module' => 'frontend', 'controller' => 'project', 'action' => 'close')
);

/** 任务/推进 */
$conf['rules'][] = array(
	'/project/advanced/:p_id',
	array('module' => 'frontend', 'controller' => 'project', 'action' => 'advanced')
);
/** frontend/project end */

/** frontend/vote begin */
/** 微评选/列表 */
$conf['rules'][] = array(
	'/vote',
	array('module' => 'frontend', 'controller' => 'vote', 'action' => 'list')
);

$conf['rules'][] = array(
	'/vote/list',
	array('module' => 'frontend', 'controller' => 'vote', 'action' => 'list')
);

/** 微评选/新增 */
$conf['rules'][] = array(
	'/vote/new',
	array('module' => 'frontend', 'controller' => 'vote', 'action' => 'new')
);

/** 微评选/查看 */
$conf['rules'][] = array(
	'/vote/view/:v_id',
	array('module' => 'frontend', 'controller' => 'vote', 'action' => 'view')
);

/** 微评选/投票 */
$conf['rules'][] = array(
	'/vote/choice/:v_id',
	array('module' => 'frontend', 'controller' => 'vote', 'action' => 'choice')
);
/** frontend/vote end */

/** frontend/dailyreport begin */
/** 报告/搜索 */
$conf['rules'][] = array(
	'/dailyreport/so',
	array('module' => 'frontend', 'controller' => 'dailyreport', 'action' => 'search')
);

/** 报告/搜索 */
$conf['rules'][] = array(
	'/dailyreport/so/:ac',
	array('module' => 'frontend', 'controller' => 'dailyreport', 'action' => 'search')
);

/** 报告/新增 */
$conf['rules'][] = array(
	'/dailyreport/new',
	array('module' => 'frontend', 'controller' => 'dailyreport', 'action' => 'new')
);

/** 报告/查看 */
$conf['rules'][] = array(
	'/dailyreport/view/:dr_id',
	array('module' => 'frontend', 'controller' => 'dailyreport', 'action' => 'view')
);

/** 报告/回复 */
$conf['rules'][] = array(
	'/dailyreport/reply/:dr_id',
	array('module' => 'frontend', 'controller' => 'dailyreport', 'action' => 'reply')
);
/** frontend/dailyreport end */

/** frontend/vnote begin */
/** 备忘/搜索 */
$conf['rules'][] = array(
	'/vnote/so',
	array('module' => 'frontend', 'controller' => 'vnote', 'action' => 'search')
);

/** 备忘/搜索 */
$conf['rules'][] = array(
	'/vnote/so/:ac',
	array('module' => 'frontend', 'controller' => 'vnote', 'action' => 'search')
);

/** 备忘/新增 */
$conf['rules'][] = array(
	'/vnote/new',
	array('module' => 'frontend', 'controller' => 'vnote', 'action' => 'new')
);

/** 备忘/查看 */
$conf['rules'][] = array(
	'/vnote/view/:vn_id',
	array('module' => 'frontend', 'controller' => 'vnote', 'action' => 'view')
);

/** 备忘/编辑 */
$conf['rules'][] = array(
	'/vnote/edit/:vn_id',
	array('module' => 'frontend', 'controller' => 'vnote', 'action' => 'edit')
);

/** 备忘/删除 */
$conf['rules'][] = array(
	'/vnote/delete/:vn_id',
	array('module' => 'frontend', 'controller' => 'vnote', 'action' => 'delete')
);
/** frontend/vnote end */

/** frontend/minutes begin */
/** 会议纪要/搜索 */
$conf['rules'][] = array(
	'/minutes/so',
	array('module' => 'frontend', 'controller' => 'minutes', 'action' => 'search')
);

/** 会议纪要/新增 */
$conf['rules'][] = array(
	'/minutes/new',
	array('module' => 'frontend', 'controller' => 'minutes', 'action' => 'new')
);

/** 会议纪要/查看 */
$conf['rules'][] = array(
	'/minutes/view/:mi_id',
	array('module' => 'frontend', 'controller' => 'minutes', 'action' => 'view')
);

/** 会议纪要/回复 */
$conf['rules'][] = array(
	'/minutes/reply/:mi_id',
	array('module' => 'frontend', 'controller' => 'minutes', 'action' => 'reply')
);
/** frontend/minutes end */

/** frontend/notice begin */
/** 公告/列表 */
$conf['rules'][] = array(
	'/notice',
	array('module' => 'frontend', 'controller' => 'notice', 'action' => 'list')
);
/** 公告/列表 */
$conf['rules'][] = array(
	'/notice/list',
	array('module' => 'frontend', 'controller' => 'notice', 'action' => 'list')
);

/** 公告/新增 */
$conf['rules'][] = array(
	'/notice/new',
	array('module' => 'frontend', 'controller' => 'notice', 'action' => 'new')
);

/** 公告/查看 */
$conf['rules'][] = array(
	'/notice/view/:nt_id',
	array('module' => 'frontend', 'controller' => 'notice', 'action' => 'view')
);
/** 备忘/搜索 */
$conf['rules'][] = array(
	'/notice/so',
	array('module' => 'frontend', 'controller' => 'notice', 'action' => 'list')
);
/** frontend/notice end */

/** frontend/reimburse begin */
/** 报销/列表 */
$conf['rules'][] = array(
	'/reimburse',
	array('module' => 'frontend', 'controller' => 'reimburse', 'action' => 'list')
);

$conf['rules'][] = array(
	'reimburse/list',
	array('module' => 'frontend', 'controller' => 'reimburse', 'action' => 'list')
);

/** 报销/搜索 */
$conf['rules'][] = array(
	'reimburse/so',
	array('module' => 'frontend', 'controller' => 'reimburse', 'action' => 'search')
);

/** 报销/新增 */
$conf['rules'][] = array(
	'/reimburse/new',
	array('module' => 'frontend', 'controller' => 'reimburse', 'action' => 'new')
);

/** 报销/查看 */
$conf['rules'][] = array(
	'/reimburse/view/:rb_id',
	array('module' => 'frontend', 'controller' => 'reimburse', 'action' => 'view')
);

/** 报销/同意 */
$conf['rules'][] = array(
	'/reimburse/approve/:rb_id',
	array('module' => 'frontend', 'controller' => 'reimburse', 'action' => 'verify_approve')
);

/** 报销/拒绝 */
$conf['rules'][] = array(
	'/reimburse/refuse/:rb_id',
	array('module' => 'frontend', 'controller' => 'reimburse', 'action' => 'verify_refuse')
);

/** 报销/同意并转审批 */
$conf['rules'][] = array(
	'/reimburse/transmit/:rb_id',
	array('module' => 'frontend', 'controller' => 'reimburse', 'action' => 'verify_transmit')
);

/** 报销/清单/新增 */
$conf['rules'][] = array(
	'/reimburse/bill/new',
	array('module' => 'frontend', 'controller' => 'reimburse', 'action' => 'bill_new')
);

/** 报销/清单/查看 */
$conf['rules'][] = array(
	'/reimburse/bill/view/:rbb_id',
	array('module' => 'frontend', 'controller' => 'reimburse', 'action' => 'bill_view')
);

/** 报销/清单/编辑 */
$conf['rules'][] = array(
	'/reimburse/bill/edit/:rbb_id',
	array('module' => 'frontend', 'controller' => 'reimburse', 'action' => 'bill_edit')
);
/** 报销/清单/删除 */
$conf['rules'][] = array(
	'/reimburse/bill/del/:rbb_id',
	array('module' => 'frontend', 'controller' => 'reimburse', 'action' => 'bill_del')
);

/** 报销/清单/列表 */
$conf['rules'][] = array(
	'/reimburse/bill/list',
	array('module' => 'frontend', 'controller' => 'reimburse', 'action' => 'bill_list')
);
/** frontend/reimburse end */

/** frontend/attachment begin */
/** 附件/上传 */
$conf['rules'][] = array(
	'/attachment/upload',
	array('module' => 'frontend', 'controller' => 'attachment', 'action' => 'upload')
);

/** 附件/读取 */
$conf['rules'][] = array(
	'/attachment/read/:at_id',
	array('module' => 'frontend', 'controller' => 'attachment', 'action' => 'read')
);

/** 附件/读取 */
$conf['rules'][] = array(
	'/attachment/read/:at_id/:wh',
	array('module' => 'frontend', 'controller' => 'attachment', 'action' => 'read')
);
/** frontend/attachment end */

/** 联合登录/微信 */
$conf['rules'][] = array(
	'/member/wechat',
	array('module' => 'frontend', 'controller' => 'member', 'action' => 'wechat')
);