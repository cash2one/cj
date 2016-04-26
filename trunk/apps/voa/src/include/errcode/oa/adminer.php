<?php
/**
 * adminer.php
 * 管理员
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_errcode_oa_adminer {

	const ADMINER_LOCKED = '101:帐号被锁定禁止登录';
	const ADMINER_FORBID = '102:帐号或密码错误';
	const ADMINER_ACCOUNT_NOT_EXISTS = '103:用户信息不存在或已被删除（%s）';
	const ADMINER_ACCOUNT_ERROR = '104:只允许手机号或者邮箱地址登录';
	const ADMINERGROUP_NOT_EXISTS = '105:所在管理组不存在';
	const ADMINERGROUP_DISABLED = '106:所在管理组禁止登录';
	const ADMINER_ID_NOT_EXISTS = '107:管理员身份不存在';

}
