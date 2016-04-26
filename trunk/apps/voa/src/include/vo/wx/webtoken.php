<?php
/**
 * 微信 web access token 数据结构
 * $Author$
 * $Id$
 */


class voa_vo_wx_webtoken extends vo {
	/** 网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同 */
	public $access_token;
	/** access_token接口调用凭证超时时间，单位（秒） */
	public $expires_in;
	/** 用户刷新access_token */
	public $refresh_token;
	/** 用户 openid */
	public $openid;
	/** 用户授权的作用域，使用逗号（,）分隔 */
	public $scope;
}
