<?php
/**
 * voa_errcode_api_talk
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

class voa_errcode_api_talk {

	const VIEWER_IS_NOT_EXISTS = '1001001:客户不存在';

}
