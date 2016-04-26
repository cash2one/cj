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
class voa_errcode_api_inspect {

	const NO_PRIVILEGE = '90601:没有权限';
	const INSPECT_ITEM_IS_NOT_EXIST = '90602:不存在或已删除';
	const INSPECT_ITEM_ERROR = '90603:该项目不存在';
	const ALL_ITEM_SCORE_IS_REQUIRE = '90604:还有未巡店项目';
	const UNDEFINED_ACTION = '90605:非法请求';
	const NEW_SCORE_NULL = '90606:分值不能为空';
	const NEW_MEM_UIDS_NULL = '90607:目标人不能为空';
	const INSPECT_IS_NOT_EXIST = '90608:巡店信息不存在或已删除';
	const LIST_UNDEFINED_FUNCTION = '90609:非法请求';
	/*const NEW_MEM_UIDS_NULL = '90607:目标人不能为空';*/



}
