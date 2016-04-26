<?php
/**
 * voa_errcode_api_auth
 * 认证登录相关的错误代码库
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_errcode_api_auth {

	const AUTH_ACCOUNT_UNKNOW = '40001:登录帐号应该为手机号或邮箱地址';

	/** 登录帐号不存在 */
	const AUTH_MEMBER_NOT_EXISTS = '40002:登录帐号或密码错误';
	/** 登录帐号被标记为删除状态 */
	const AUTH_MEMBER_FORBID = '40003:登录帐号或密码错误';
	/** 密码不是以md5方式提交 */
	const AUTH_PASSWORD_NOT_MD5 = '40004:登录帐号或密码错误';
	/** 密码输入错误 */
	const AUTH_PASSWORD_ERROR = '40005:登录帐号或密码错误';

	/** code值为空 */
	const WECHAT_CODE_NULL = '40006:参数值丢失';

}

