<?php
/**
 * 微信 snsapi userinfo 数据结构
 * $Author$
 * $Id$
 */


class voa_vo_wx_userinfo extends vo {
	/** 用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息。 */
	public $subscribe;
	/** 用户的标识，对当前公众号唯一 */
	public $openid;
	/** 用户的昵称 */
	public $nickname;
	/** 用户的性别，值为1时是男性，值为2时是女性，值为0时是未知 */
	public $sex;
	/** 用户所在城市 */
	public $city;
	/** 用户所在国家 */
	public $country;
	/** 用户所在省份 */
	public $province;
	/** 用户的语言，简体中文为zh_CN */
	public $language;
	/** 用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空 */
	public $headimgurl;
	/** 用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间 */
	public $subscribe_time;
}
