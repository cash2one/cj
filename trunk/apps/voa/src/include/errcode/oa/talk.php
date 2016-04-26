<?php
/**
 * voa_errcode_oa_talk
 * OA 聊天相关 呼叫错误码
 * 使用7位编码
 *
 * $Author$
 * $Id$
 */

class voa_errcode_oa_talk {

	const PLEASE_SELECT_SALES = '1006000:请选择需要咨询的人员';
	const PLEASE_REFRESH = '1006001:请刷新';
	const MESSAGE_IS_NULL = '1006002:消息内容不能为空';
	const SALES_UID_IS_NULL = '1006003:请刷新, 重新登录';
	const VIEWER_UID_IS_NULL = '1006004:请刷新';
}
