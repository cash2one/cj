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
class voa_errcode_api_file {

	/** 无法自cookie信息读取用户身份 */
	const USER_NOT_LOGIN = '90001:非法的用户身份';
	/** 读取文件所属用户 */
	const USER_NOT_PERMISSION = '90002:没有权限操作此文件';
	

}
