<?php
/**
 * voa_errcode_api_addressbook
 * API 接口通信录错误码定义
 * 约定错误码均以1开头的5位数字
 * 每个错误码以常量定义，格式为“errcode:errmsg”
 * errcode为唯一的整型
 * errmsg为错误信息，可使用%s做为变量
 * 一旦提供文档，错误码errcode不允许变更！！
 * $Author$
 * $Id$
 */
class voa_errcode_api_dailreport {

	const NEW_MESSAGE_NULL = '90101:报告内容不能空';
	const NEW_REPORTTIME_NULL = '90102:报告时间不能为空';
	const NEW_APPRAVEUID_NULL = '90103:接收人不能为空';
	const NEW_APPRAVEUID_SET_NULL = '90104:接收人不能是报告发送人';

	const VIEW_NOT_EXISTS = '90105:指定报告不存在或已删除（%s）';
	const VIEW_NO = '90106:您没有权限查看';
	const APPROVEUSER_IS_EMPTY = '90107:接收人不能为空';
	const DAILYREPORT_NEW_FAILED = '90108:报告入库失败';
	const LIST_UNDEFINED_ACTION = '90109:非法操作';


}
