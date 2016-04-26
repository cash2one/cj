<?php
/**
 * voa_errcode_uc_member
 * uc用户相关错误代码库
 * 以8开头的4位整形
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_errcode_uc_member {

	const MOBILE_ERROR = '8000:手机号码格式错误';
	const MOBILE_USED = '8001:手机号码已被注册';
	const EMAIL_ERROR = '8002:邮箱地址格式错误';
	const EMAIL_USED = '8003:邮箱地址已被注册';
	const REALNAME_ERROR = '8004:真实姓名格式错误';
	const MEMBER_INSERT_FAILED = '8005:注册新用户发生数据错误';
	const PASSWORD_IS_NOT_MD5 = '8006:密码格式错误';
}
