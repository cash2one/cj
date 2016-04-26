<?php
/**
 * voa_errcode_oa_member
 * OA member相关 呼叫错误码
 * 使用7位编码
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_errcode_oa_member {

	/** 待修改密码的帐号不存在 */
	const MEMBER_UDA_UPDATE_PWD_MODIFY_DATA_NULL = '1000001:账号不存在%s';
	/** 发生数据库错误导致密码更改失败 */
	const MEMBER_UDA_UPDATE_PWD_MODIFY_FAILED = '1000002:修改密码失败';
	/** 无法读取指定 m_uid 的用户信息 */
	const MEMBER_NOT_EXISTS = '1000003:用户信息不存在或已被删除';
	/** 只能使用手机号、邮箱等登录帐号来获取用户信息 */
	const MEMBER_ACCOUNT_ERROR = '1000004:未知的登录帐号信息';
	/** 指定的登录帐号不存在 */
	const MEMBER_ACCOUNT_NOT_EXISTS = '1000005:用户信息不存在或已被删除（%s）';
	const MEMBER_UID_NULL = '1000006:指定UID不存在';
	const MEMBER_DELETE_FAILED = '1000007:删除用户操作失败';

	/** 添加新用户必须要填写的字段 */
	const MEMBER_FIELD_LOSE = '1000008:%s 必须填写';
	/** 必须使用正确的手机号码字符串 */
	const MEMBER_MOBILE_FORMAT_ERROR = '1000009:手机号码格式错误';
	/** 手机号已被其他人绑定过，不能再次绑定 */
	const MEMBER_MOBILE_USED = '1000010:手机号码已被登记过不能再次使用';
	/** 必须使用正确邮箱字符串 */
	const MEMBER_EMAIL_FORMAT_ERROR = '1000011:邮箱格式错误';
	/** 邮箱地址已被其他人绑定过，不能再次绑定 */
	const MEMBER_EMAIL_USED = '1000012:邮箱地址已被登记过不能再次使用';
	/** 真实姓名必须介于3到45字节之间，且不允许使用特殊字符 */
	const MEMBER_USERNAME_FORMAT_ERROR = '1000013:真实姓名格式错误';
	/** 未设定主部门ID或者主部门ID为空 */
	const MEMBER_DEPARTMENT_NULL = '1000014:必须设置所属的主部门';
	/** 指定的主部门ID不存在 */
	const MEMBER_DEPARTMENT_NOT_EXISTS = '1000015:设置的主部门不存在或已删除[%s]';
	/** 在职状态ID值设置错误 */
	const MEMBER_ACTIVE_ERROR = '1000016:未知的在职状态[%s]';
	/** 参数数据不是标准的在职状态文字（在职、离职） */
	const MEMBER_ACTIVE_STRING_ERROR = '1000017:未知的在职状态文字[%s]';
	/** 参数数据不是数字或者字符串 */
	const MEMBER_ACTIVE_FORMAT_ERROR = '1000018:在职状态设置错误';
	const MEMBER_DEPARTMENT_NAME_NULL = '1000019:部门名称不能为空';
	const MEMBER_DEPARTMENT_NAME_NOT_EXISTS = '1000020:部门名称不存在';
	/** 只能使用数字或者字符串 */
	const MEMBER_DEPARTMENT_FORMAT_ERROR = '1000021:部门数据格式错误';
	const MEMBER_NUMBER_ERROR = '1000022:工号设置错误，只允许9位以内的正整数';

	/** 未设定主职务ID或者主职务ID为空 */
	const MEMBER_JOB_NULL = '1000014:必须设置所担任的职位';
	/** 指定的主职务ID不存在 */
	const MEMBER_JOB_NOT_EXISTS = '1000015:设置的主职位不存在或已删除[%s]';
	const MEMBER_JOB_NAME_NULL = '1000019:职位名称不能为空';
	const MEMBER_JOB_NAME_NOT_EXISTS = '1000020:职位名称不存在';

	const MEMBER_ADDRESS_ERROR = '1000021:住址长度只能设置 250个字节 且不允许包含特殊字符';
	const MEMBER_IDCARD_ERROR = '1000022:身份证号码格式错误';
	const MEMBER_TELETEPHONE_ERROR = '1000023:电话号码格式错误';
	const MEMBER_QQ_ERROR = '1000024:QQ号码格式错误';
	const MEMBER_WEIXINID_ERROR = '1000025:微信号码格式错误';
	const MEMBER_WEIXINID_UESED = '1000026:微信号已被其他人登记过';
	const MEMBER_REMARK_ERROR = '1000027:备忘信息长度应该限制小于 255个字符以内且不允许特殊字符';
	const MEMBER_PASSWORD_NEW_NOT_MD5 = '1000028:密码需要使用32位的md5格式';

	const MEMBER_INSERT_FAILED = '1000029:添加新员工操作失败';
	/** 本地不存在企业微信部门的对应ID，尝试添加到企业微信数据失败 */
	const MEMBER_ADD_DEPARTMENT_TO_QYWX = '1000030:部门ID获取失败';
	const MEMBER_QYWX_USERID_NONE = '1000031:无法获取到员工唯一标识符';

	const MEMBER_WECHAT_ACCOUNT_NULL = '1000032:帐号不能为空';

	const MEMBER_WECHAT_BIND_NOT_UNIQUE = '1000033:该微信号已经绑定过其他帐号不能再次绑定';

	const MEMBER_WECHAT_UID_NOT_EXISTS = '1000034:指定用户不存在';

	const MEMBER_WECHAT_BIND_EQUALLY = '1000035:帐号已经绑定过微信不需要再次绑定';

	/** 用户已被标记为删除状态 */
	const MEMBER_FORBID = '1000036:登录帐号或密码错误';
	/** unionid加密字符串超时 */
	const UNIONID_DECODE_TIMEOUT = '1000037:微信用户信息获取超时';
	/** unonid加密字符串构造错误 */
	const UNIONID_DECODE_RANDOM_NOT_EXISTS = '1000038:微信用户信息获取错误';
	/** unionid加密字符串非法 */
	const UNIONID_DECODE_IS_NOT_STRING = '1000039:微信用户信息获取错误';

	// 微信号/手机/邮箱不能同事为空
	const MOBILE_EMAIL_WEIXINID_IS_EMPTY = '1000040:微信号/手机/邮箱不能同时为空';

	const UID_IS_NULL = '1000041:UID不能为空';


    const POSITION_NOT_EXISTS = '1000042:职务不存在';
    const POSITION_PARENT_ID_NOT_EXISTS = '1000043:父级职务不存在';
    const POSITION_NAME_ERROR = '1000044:职务名称不能包含特殊字符';
}
