<?php
/**
 * class voa_errcode_uc_pwd
 * 找回密码错误码
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_errcode_uc_pwd {

	/** 用户帐号只能是 手机号 或 邮箱地址 */
	const PWD_ACCOUNT_UNKNOW = '4501:请使用 手机号 或 邮箱地址 来找回密码';
	/** 该企业内无用此email登记的帐号信息 */
	const PWD_EMAIL_NOT_EXISTS = '4502:邮箱地址不存在%s';
	/** email请求重置密码的 hash 值错误 */
	const PWD_EMAIL_RESET_HASH_ERROR = '4503:非法的密码重置请求';
	/** email请求重置密码的 key 与 hash 不匹配 */
	const PWD_EMAIL_RESET_KEY_HASH_ERROR = '4504:非法的密码重置请求';
	/** email请求重置密码的 key 非法 */
	const PWD_EMAIL_RESET_KEY_ILL = '4505:非法的密码重置请求';
	/** email请求重置密码的 key 已经超时失效 */
	const PWD_EMAIL_RESET_TIMEOUT = '4506:密码重置链接已失效';
	/** 短信验证码格式不正确 */
	const PWD_SMSCODE_FORMAT_ERROR = '4507:短信验证码错误';
	/** 新密码请求值非标准 md5 字符串 */
	const PWD_PASSWORD_FORMAT_ERROR = '4508:新密码设置错误%s';
	/** 无法找到该手机的短信验证码信息 */
	const PWD_SMSCODE_NONE = '4509:短信验证码错误';
	/** 短信验证码已失效 */
	const PWD_SMSCODE_TIMEOUT = '4510:短信验证码错误';
	/** 短信验证码不正确 */
	const PWD_SMSCODE_ERROR = '4511:短信验证码错误';

}
