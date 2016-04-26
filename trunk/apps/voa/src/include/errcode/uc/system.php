<?php
/**
 * voa_errcode_uc_system
 * UC API 接口全局公共错误码定义
 * UC 约定错误码均以4开头的4位数字
 * 每个错误码以常量定义，格式为“errcode:errmsg”
 * errcode为唯一的整型
 * errmsg为错误信息，可使用%s做为变量
 * 一旦提供文档，错误码errcode不允许变更！！
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_errcode_uc_system {

	const UC_OK = '0:ok';
	const UC_UNKNOWN = '4001:系统未知错误';
	const UC_FUNCTION_UNKNOW = '4002:未知资源请求';
	const UC_FUNCTION_UNSET = '4003:未定义的资源 %s 请求';
	const UC_FUNCTION_ILLEGAL = '4004:非法的资源名称 %s_%s';
	const UC_PARAM_LOSE = '4005:缺少参数 "%s"';

	const UC_MOBILE_EMPTY = '4006:手机号码必须填写%s';
	const UC_MOBILE_ERROR = '4007:手机号码填写错误%s';
	const UC_MOBILE_EXISTS = '4008:手机号码已被使用';

	const UC_REALNAME_EMPTY = '4009:真实姓名必须填写';
	const UC_REALNAME_ERROR = '4010:真实姓名填写错误%s';

	const UC_EMAIL_EMPTY = '4011:邮箱地址必须填写';
	const UC_EMAIL_ERROR = '4012:邮箱地址填写错误';
	const UC_EMAIL_EXISTS = '4013:邮箱地址已被使用';

	const UC_ENAME_EMPTY = '4014:公司账号必须填写';
	const UC_ENAME_ERROR = '4015:公司账号填写错误%s';

	const UC_ENUMBER_EMPTY = '4016:企业号必须设置';
	/** 企业号格式不正确 */
	const UC_ENUMBER_ERROR = '4017:企业号填写错误%s';
	const UC_ENUMBER_EXISTS = '4018:企业号已被占用';

	const UC_PASSWORD_EMPTY = '4019:登录密码必须设置';
	const UC_PASSWORD_LENGTH_ERROR = '4020:登录密码设置错误%s';

	const UC_WXUNIONID_EXISTS = '4021:微信帐号已注册过企业号';

	const UC_WEB_HOST_NOT_EXISTS = '4022:指定的 web 主机不存在[%s]';
	const UC_WEB_HOST_EMPTY = '4023:WEB 主机池无备选主机';

	const UC_DB_HOST_NOT_EXISTS = '4024:指定的 DB 主机不存在[%s]';
	const UC_DB_HOST_EMPTY = '4025:DB 主机池无备选主机';

	const UC_OPEN_ENTERPRISE_DB_ERROR = '4026:写入企业信息发生数据错误';
	const UC_OPEN_ENTERPRISE_EP_ID_NONE = '4027:登记企业信息发生错误';
	/** 使用 validator::is_realname 进行验证 */
	const UC_OPEN_ENTERPRISE_REALNAME_LENGTH = '4028:真实姓名长度应该介于 3到20 字节之间';
	const UC_OPEN_ENTERPRISE_REALNAME_FORMAT = '4029:真实姓名不能包含特殊字符';
	const UC_OPEN_ENTERPRISE_NAME_LENGTH = '4030:公司账号应该小于 50 个字';
	const UC_OPEN_ENTERPRISE_NAME_FORMAT = '4031:公司账号不能包含特殊字符';

	/** 检查企业号，企业号格式不正确 */
	const UC_ENUMBER_CHECK_ERROR = '4032:企业号格式错误';
	/** 检查企业号，企业号不存在 */
	const UC_ENUMBER_CHECK_NOT_EXISTS = '4033:企业号不存在';
	/** 指定的企业号在黑名单内，禁止被注册使用 */
	const UC_ENUMBER_BLACKLIST = '4034:企业号已被使用请更换一个';

	/** 登录帐号或登录密码未填写 */
	const LOGIN_INPUT_NULL = '4035:登录帐号或登录密码错误';

	const LOGIN_ACCOUNT_UNKNOWN = '4036:请使用手机号或邮箱登录';

	/** 未找到指定手机号的用户信息 */
	const LOGIN_MOBILE_MEMBER_NULL = '4037:登录帐号或登录密码错误';
	/** 未找到指定邮箱的用户信息 */
	const LOGIN_EMAIL_MEMBER_NULL = '4038:登录帐号或登录密码错误';
	/** 密码格式非32位md5字符串 */
	const LOGIN_PASSWORD_NOT_MD5 = '4039:登录帐号或登录密码错误';
	/** 密码验证错误 */
	const LOGIN_PASSWORD_ERROR = '4040:登录帐号或登录密码错误';
	/** 找不到指定m_id的用户信息 */
	const LOGIN_ID_NOT_EXISTS = '4041:登录帐号或登录密码错误';
}
