<?php
/**
 * voa_errcode_rpc_member
 * OA RPC member 相关呼叫错误码
 * 使用8位编码
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_errcode_rpc_member {

	/** 未请求任何参数 */
	const OARPC_MEMBER_PWDRESET_PARAM_NULL = '10000001:参数请求错误';
	/** 缺少请求的参数 */
	const OARPC_MEMBER_PWDRESET_PARAM_LOSE = '10000002:参数缺失';
	/** 帐号既不是手机号也不是邮箱 */
	const OARPC_MEMBER_PWDRESET_ACCOUNT_UNKNOW = '10000003:未知的账号类型';
	/** 指定的帐号不存在或已被删除 */
	const OARPC_MEMBER_PWDRESET_ACCOUNT_NOT_EXISTS = '10000004:帐号不存在';

}
