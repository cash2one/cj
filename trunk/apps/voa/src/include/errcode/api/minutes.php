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
class voa_errcode_api_minutes {

	const NEW_SUBJECT_NULL = '90401:会议记录主题不能空';
	const NEW_MESSAGE_NULL = '90402:会议记录内容不能为空';
	const NEW_RECVUIDS_NULL = '90403:会议记录接收人不能为空';
	const NEW_RECVUIDS_SET_NULL = '90404:会议记录接收人不能是自己';
	const MINUTES_NEW_FAILED = '90405:新建记录失败';

	const MINUTES_IS_NOT_EXISTS = '90406:会议记录不存在或已删除';
	const NO_PRIVILEGE = "90407:没有权限查看";
	const BEGINTIME_OR_ENDTIIME_ERROR = "90208:时间设置错误";

	const MESSAGE_TOO_SHORT = "90209:回复信息太短";




}
