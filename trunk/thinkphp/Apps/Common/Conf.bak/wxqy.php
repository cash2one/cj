<?php
/**
 * wxqy.php
 * 微信企业号配置
 * $Author$
 */

return array(
	// 过期时长
	'expires_in' => 7200,
	// 错误码
	'access_token_errcode' => array(40001, 42001, 40029),
	// 用户信息和本地数据库字段对照表
	'local2qywx_map' => array(
		'm_openid' => 'userid',
		'm_username' => 'name',
		'm_mobilephone' => 'mobile',
		'mf_telephone' => 'tel',
		'm_email' => 'email',
		'mf_weixinid' => 'weixinid',
		'cj_id' => 'position',
		'cd_id' => 'department',
		'm_gender' => 'gender',
		'm_avatar' => 'avatar'
	)
);
