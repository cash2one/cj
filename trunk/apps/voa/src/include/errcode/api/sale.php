<?php
/**
 * voa_errcode_api_sale
 * API 接口通信录错误码定义
 * 约定错误码均以1开头的5位数字
 * 每个错误码以常量定义，格式为“errcode:errmsg”
 * errcode为唯一的整型
 * errmsg为错误信息，可使用%s做为变量
 * 一旦提供文档，错误码errcode不允许变更！！
 * $Author$
 * $Id$
 */
class voa_errcode_api_sale {

	const COUSTMER_NULL = '90600:查询不到详情';
	const COUSTMER_LIST_NULL = '90601:暂无数据';
	const TRAJECTORY_NULL = '90602:查询不到详情';
	const TRAJECTORY_LIST_NULL = '90603:暂无数据';
	const NO_PERMISSIONS = '90604:没有权限';
}
