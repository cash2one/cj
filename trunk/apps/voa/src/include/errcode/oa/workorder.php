<?php
/**
 * workorder.php
 * 派单错误码
 * 2xxx 4位整型
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_errcode_oa_workorder {

	const WOSTATE_ERROR = '2001:工单状态选择错误';
	const REMARK_LENGTH_ERROR = '2002:工单备注内容长度应该介于 %s 到 %s 个字符之间';
	const REMARK_LENGTH_MAX_ERROR = '2003:工单备注内容长度不能超过 %s 个字符';
	/** 给定接单人数据非法 */
	const OPERATOR_UID_ERROR = '2004:接单人不存在';
	const OPERATOR_UID_NULL = '2005:必须选择接单人';
	const OPERATOR_UID_DBNULL = '2006:指定接单人不存在';
	const CONTACTER_STRING_ERROR = '2007:联系人不能包含特殊字符';
	const CONTACTER_LENGTH_ERROR = '2008:联系人长度应该介于 %s 到 %s 个字符之间';
	const CONTACTER_LENGTH_MAX_ERROR = '2009:联系人长度不能超过 %s 个字符';
	const PHONE_STRING_ERROR = '2010:请正确输入联系电话';
	const PHONE_LENGTH_ERROR = '2011:联系电话长度应该介于 %s 到 %s 个字符之间';
	const PHONE_LENGTH_MAX_ERROR = '2012:联系电话不能超过 %s 个字符';
	const ADDRESS_STRING_ERROR = '2013:请正确填写联系地址';
	const ADDRESS_LENGTH_ERROR = '2014:联系地址长度应该介于 %s 到 %s 个字符之间';
	const ADDRESS_LENGTH_MAX_ERROR = '2015:联系地址长度不能超过 %s 个字符';
	const CAPTION_STRING_ERROR = '2016:执行说明文字不能包含特殊字符';
	const CAPTION_LENGTH_ERROR = '2017:执行说明内容长度应该介于 %s 到 %s 个字符之间';
	const CAPTION_LENGTH_MAX_ERROR = '2018:执行说明内容不能超过 %s 个字符';
	const WORSTATE_ERROR = '2019:执行状态设置错误';
	const WORKORDER_UID_NULL = '2020:派单人不能为空';
	const SEND_WORKORDER_PARAM_LOSE = '2021:新建工单缺少参数 %s';
	const SEND_WORKORDER_DB_ERROR = '2022:新建工单发生数据错误';
	const WORKORDER_NOT_EXISTS = '2023:工单不存在(ID: %s)';
	const OPERATOR_POWER_NONE_WORKORDER = '2024:无权查看工单 - 工单不存在';
	const OPERATOR_POWER_NULL = '2025:无权查看工单 - 工单执行人不存在';
	const OPERATOR_POWER_NOT_SENDER = '2026:无权查看工单 - 不是派单人';
	const OPERATOR_POWER_NO_WORKORDER = '2027:无权查看工单 - 未知的工单';
	const USER_NOT_OPERATOR = '2028:无权查看工单 - 您不是接单者';
	const WORKORDER_WORKING = '2029:工单已被其他人接单执行，您无权查看';
	const WORKORDER_ID_NULL = '2030:指定工单不存在 - 工单ID未知';
	const WORKORDER_REQUEST_UID_NULL = '2031:工单执行人不能为空';

	const OPERATE_WORKORDER_ID_NULL = '2032:必须指定待操作的工单ID';
	const OPERATE_WORKORDER_REQUEST_UID_NULL = '2033:请求执行的收单人必须指定';
	const OPERATE_ACTION_UNKNOW = '2034:未知的操作动作（%s）';
	const OPERATE_WORKORDER_NOT_EXISTS = '2035:指定操作的工单不存在（ID: %s）';
	const OPERATE_WORKORDER_CANCEL = '2036:指定工单已被撤销，不可操作: %s';
	const OPERATE_WORKORDER_COMPLETE = '2037:指定工单已执行完成，不可操作: %s';
	const OPERATE_ACTION_NO_POWER = '2038:无权进行此操作: %s';
	const OPERATE_WORKORDER_DB_ERROR = '2039:执行工单操作状态发生数据错误';
	const OPERATE_REFUSE_REASON_NULL = '2040:拒绝接单必须提供拒绝原因';
	const REASON_LENGTH_ERROR = '2041:拒绝原因文字长度应该介于 %s 到 %s 个字符之间';
	const REASON_LENGTH_MAX_ERROR = '2042:拒绝原因文字长度不能超过 %s 个字符';
	const OPERATOR_CAPTION_NULL = '2043:工单完成说明不能为空';

	const LIST_REQUEST_TYPE_ERROR = '2044:请求的工单列表类型未知';
	const LIST_REQUEST_PARAM_LOSE = '2045:获取列表数据请求缺失 %s 参数';
	const LIST_REQUEST_SOURCE_UNKNOW = '2046:未知的请求数据来源 %s';

	const SEARCH_VALIDATOR_METHOD_NOT_EXISTS = '2047:系统错误，校验方法 "%s" 不存在';
	const OPERATOR_ATID_ERROR = '2048:提交附件ID非法';
	const OPERATOR_ATID_NULL = '2049:没有有效的附件ID';
	const OPERATOR_ATTACHMENT_ERROR = '2050:提交的附件非法';
	const OPERATOR_NOT_OTHER = '2051:您是工单的唯一接收者，不能取消工单';
	const OPERATOR_ATTACHMENT_ROLE_ERROR = '2052:附件上传角色选择错误 %s';
	const SEND_VALIDATOR_METHOD_NOT_EXISTS = '2053:数据校验方法不存在 %s';
	const COMPLETE_ATTACHMENT_REQUIRED = '2054:必须上传照片文件';
	const COMPLETE_ATTACHMENT_COUNT_ERROR = '2055:必须至少上传 %s 个照片';
	const COMPLETE_ATTACHMENT_COUNT_MAX = '2056:系统限制最多允许上传 %s 个照片，您已超出';

	const SEND_DO_NOT_SEND_SELF = '2057:不能给自己派单';

}
