<?php
/**
 * voa_errcode_wechat_login
 * 微信联合登录错误代码
 * 编码为9位整形11xxxxxxx
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_errcode_wechat_login {

	/** 返回回调GET无state参数 */
	const WECHAT_LOGIN_STATE_NULL = '110000001:返回 CODE 错误';

	/** 获取微信token值发生连接错误 */
	const WECHAT_LOGIN_GET_ACCESS_TOKEN_FAILED = '110000002:获取微信授权发生错误';

	/** 获取微信token值发生错误，具体错误代码为微信返回值 //open.weixin.qq.com/cgi-bin/readtemplate?t=resource/wx_login_code_tmpl&lang=zh_CN */
	const WECHAT_LOGIN_GET_ACCESS_TOKEN_ERROR = '110000003:获取微信授权发生错误，错误代码[%s]';

	/** 连接unionid的url发生网络故障 */
	const WECHAT_LOGIN_GET_UNINID_URL_FAILED = '110000004:获取微信UNIONID连接错误';

	/** 获取微信用户信息发生错误，具体错误代码为微信返回值 */
	const WECHAT_LOGIN_GET_UNIONID_URL_ERROR = '110000005:获取用户信息发生错误，错误代码[%s]';

}
