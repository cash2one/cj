<?php
/**
 * register.php
 * UC/错误编码/注册/
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_errcode_uc_register {

	const REGISTER_STEP_NOT_EXISTS = '5001:非法操作[%s]';

	const REGISTER_STEP_VERIFY_ERROR = '5002:注册校验失败';

	const REGISTER_WEBHOST_NOT_EXISTS = '5003:web 主机不存在[%s]';

	const REGISTER_DNS_FAILED = '5004:域名解析发生错误%s';

	const REGISTER_SMSCODE_ERROR = '5005:手机短信验证码错误';

	const REGISTER_SMSCODE_FORBID = '5006:提交验证发生错误';

	const REGISTER_STEP_FAILED = '5007:获取新注册企业信息发生意外错误';

	/** 官网连接 UC 发生网络错误 - from /main/operation/base */
	const REGISTER_SERVER_CONNECT_ERROR = '5008:网络连接错误，请重试';

	/** 官网连接 UC 发生错误，无法解析自 UC 返回的数据 */
	const REGISTER_SERVER_NO_PARSE = '5009:网络连接错误，请重试';

	const REGISTER_MOBILE_ERROR = '5010:手机号码填写错误';
	/** 验证码格式不正确，主要是验证码长度不对 */
	const REGISTER_SMSCODE_FORMAT_ERROR = '5011:手机短信验证码填写错误';
	/** 未找到该手机发送的验证码记录 */
	const REGISTER_SMSCODE_NONE = '5012:手机短信验证码填写错误';
	/** 短信验证码是半小时之前发出的，已经过期 */
	const REGISTER_SMSCODE_TIMEOUT = '5013:短信验证码已过期，请重新申请';
	/** 接收解析 smsauth 参数验证失败 */
	const REGISTER_SMSCODE_FORBID_ERROR = '5014:提交验证发生错误';
	const UC_MEMBER_HAVE_ENTERPRISE = '5015:每个帐号只能开启%s个企业，您已达到限制';
}
