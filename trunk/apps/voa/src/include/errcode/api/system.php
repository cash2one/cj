<?php
/**
 * voa_errcode_uc_system
 * API 接口全局公共错误码定义
 * 约定错误码均以1开头的5位数字
 * 每个错误码以常量定义，格式为“errcode:errmsg”
 * errcode为唯一的整型
 * errmsg为错误信息，可使用%s做为变量
 * 一旦提供文档，错误码errcode不允许变更！！
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_errcode_api_system {

	const API_OK = '0:ok';
	const API_UNKNOWN = '10001:系统未知错误';
	const API_OPERATION_ERROR = '10002:非法错误';
	const API_REQUEST_ERROR = '10003:请求错误';

	/** 未发现cookie内的uid数据 */
	const API_ACCESS_NO_COOKIE = '10004:身份验证失败-%s';
	/** cookie内的uid数据非法 */
	const API_ACCESS_UID_ERROR = '10005:身份验证失败';
	/** cookie内的auth数据失败 */
	const API_ACCESS_AUTH_ERROR = '10006:身份验证失败';
	const API_DB_ERROR = '10007:企业账号错误';

	/** 参数 page 必须为非零的整型 */
	const API_PARAM_PAGE_ERROR = '10008:页码数值请求错误';
	/** 参数 limit 必须为非零的整型*/
	const API_PARAM_LIMIT_ERROR = '10009:数据行数请求错误';
	/** 参数 limi 必须小于 500 */
	const API_PARAM_LIMIT_OVERFLOW = '10010:数据行数请求错误';

}
