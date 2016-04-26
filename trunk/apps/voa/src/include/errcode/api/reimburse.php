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
class voa_errcode_api_reimburse {

	const NEW_SUBJECT_NULL = '90501:报销标题不能空';
	const NEW_RBB_ID_NULL = '90502:报销明细不能为空';
	const NEW_APPROVEUID_NULL = '90503:报销审核人不能为空';
	const NEW_APPROVEUID_SET_NULL = '90504:报销审核人不能是自己';

	const RBB_ID_INVALID = '90505:指定明细不在存在或是删除';
	const REIMBURSE_NEW_FAILED = '90506:申请失败';
	const APPROVE_USER_INVALID = '90507:审核人不存在';
	const NO_PRIVILEGE = '90508:没有权限查看';
	const LIST_UNDEFINED_ACTION = '90510:非法操作';

	const NEW_TYPE_NULL = '90511:报销清单类型不能为空';
	const NEW_TIME_NULL = '90512:报销清单发现时间不能为空';
	const NEW_EXPEND_NULL = '90513:报销清单消费不能为空';
	const NEW_REASON_NULL = '90514:报销清单原因不能为空';
	const REIMBURSE_BILL_IS_NOT_EXISTS = '90515:指定明细不在存在或是删除';

	const REIMBURSE_NOT_EXIST = '90515:指定明细不在存在或是删除';
	const REIMBURSE_PROC_ERROR = '90516:无此权限';
	const REIMBURES_FORBIDDEN = '90517:无此权限';
	const NEW_MESSAGE_NULL = '90518:留言不能为空';
	const APPROVE_USER_IS_SELF = '90519:不能审批自己的报销';

	const REIMBURSE_DUPLICTE_USER = '90521:该用户不能重复审批';

}
