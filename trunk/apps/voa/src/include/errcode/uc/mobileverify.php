<?php
/**
 * mobileverify.php
 * 手机验证码错误库
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_errcode_uc_mobileverify {

	/** 同一 IP 地址请求频繁，一般为 1分钟 请求一次 */
	const UC_MV_IP_TIMES_SHORT = '6001:验证码请求太频繁，请 1分钟 后再试';

	/** 同一 手机号 请求频繁，一般为 1分钟 请求一次 */
	const UC_MV_MOBILE_TIMES_SHORT = '6002:验证码请求太频繁，请 1分钟 后再试';

	/** IP 数据加密字符串解码错误 */
	const UC_MV_IP_CRYPT_ERROR = '6003:验证码请求身份验证错误';

	/** IP 数据解码非实际 IP 字符串 */
	const UC_MV_IP_CRYPT_NONE = '6004:验证码请求身份验证错误';

	/** IP 数据解码 时间戳 有误 */
	const UC_MV_IP_CRYPT_TIME = '6005:验证码请求身份验证错误';

	/** IP 数据解码 时间超时 */
	const UC_MV_IP_CRPTY_TIMEOUT = '6006:验证码请求身份验证错误';

	/** 手机号码格式有误 */
	const UC_MV_MOBILE_ERROR = '6007:手机号码格式不正确';

	/** IP 数据时间超时 */
	const UC_MV_IP_CRYPT_TIMEOUT = '6008:验证码请求身份验证超时';

	const UC_MV_SEND_SMSCODE_UNKNOW = '6009:发送验证码发生未知错误';

}
