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
class voa_errcode_api_askoff {

	const NEW_MESSAGE_NULL = '90201:请假内容不能空';
	const NEW_TYPE_NULL = '90202:请假不能为空';
	const NEW_BEGINTIME_NULL = '90203:开始时间不能为空';
	const NEW_ENDTIME_NULL = '90204:结束时间不能为空';

	const NEW_TIME_ERROR = '90205:时间设置错误';
	const NEW_APPROVEUID_NULL = '90206:审核人不能为空';
	const NEW_APPROVEUID_SET_NULL = "90207:审核收人不能是自己";
	const BEGINTIME_OR_ENDTIIME_ERROR = "90208:时间设置错误";

	const ASKOFF_NEW_FAILED = "90209:申请失败";
	const ASKOFF_VERIFY_SELF = "90210:审核收人不能是自己";
	const APPROVEUSER_IS_EMPTY = "90211:审核人不能为空";
	const LIST_UNDEFINED_FUNCTION = "90212:非法操作";
	const ASKOFF_PROC_ERROR = "90213:没有权限查看";

	const ASKOFF_NOT_EXIST = "90214:请假信息不存在";
	const ASKOFF_FORBIDDEN = "90215:无此权限";
	const REIMBURSE_NEW_FAILED = "90216:申请失败";

	const ASKOFF_DUPLICTE_USER = "90217:该审批人已经审批";
	const NO_PRIVILEGE = "90218:无此权限";
	const APPROVEUSER_NOT_EXIST = "90219:审批人不存在";
	const NEW_APPROVE_MESSAGE_NULL = "90220:请假进度信息不能为空";
	
	
	const TYPE_NULL = "90221:请假类型不能为空";
}
