<?php
/**
 * voa_errcode_wechat_base
 * 微信开放平台公共错误代码
 * 编码为9位整形10xxxxxxx
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_errcode_wechat_base {

	/** 微信请求code返回超时 */
	const WECHAT_BASE_STATE_TIMEOUT = '100000001:CODE 获取错误';

	/** 微信请求code返回state错误 */
	const WECHAT_BASE_STATE_ERROR = '10000002:CODE 获取错误';

}
