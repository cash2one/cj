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
class voa_errcode_api_askfor {

	const SUBJECT_NULL = '90500:审批主题不能空';
	const MESSAGE_NULL = '90501:审批内容不能空';
	const APPROVEUID_NULL = '90502:审核人不能为空';
	const APPROVEUID_SET_NULL = "90503:审核人不能是自己";

	const NEW_FAILED = "90504:申请失败";
	const ASKFOR_PROC_ERROR = "90505:没有权限查看";

	const ASKFOR_NOT_EXIST = "90506:审批信息不存在";
	const ASKFOR_FORBIDDEN = "90507:无此权限";

	const ASKFOR_DUPLICTE_USER = "90508:该审批人已经审批";
	const APPROVEUSER_NOT_EXIST = "90509:审批人不存在";
	const AFT_ID_NOT_EXIST = "90510:审批流程ID不能为空";
	const TEMPLATE_NOT_EXIST = "90511:审批流程不存在";

	const LIST_UNDEFINED_FUNCTION = '方法不存在';
	const AF_ID_NULL = 'ID异常';
	const PROC_ERROR = '获取不到进度';
}
