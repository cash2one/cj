<?php
/**
 * zh-cn.php
 * 公共的语言文件
 * $Author$
 * $Id$
 */

return array(
	'COPYRIGHT' => 'copyright@2014畅移',
	'WXQY_ERRORS' => array(
		'agentid' => 40056, // agentid 错误
		'addressbook' => 60011 // 没有通讯录权限
	),
	'ALL_USER' => '全部人员',

	'_ERR_PLUGIN_IS_NOT_EXIST' => '5000:插件信息不存在',
	'_ERR_SUITEID_IS_EMPTY' => '5001:套件ID为空',
	'_ERR_UPLOAD_MEDIA_FAILED' => '5002:媒体文件上传失败',
	'_ERR_SNOOPY_STATUS_ERROR' => '5003:接口返回状态错误',
	'_ERR_SNOOPY_RESULT_EMPTY' => '5004:接口返回值为空',
	'_ERR_FILE_IS_NOT_EXIST' => '5005:文件不存在',
	'_ERR_MEDIA_ID_IS_EMPTY' => '5006:媒体文件ID',
	'_ERR_MENU_LEVEL_INVALID' => '5007:菜单级数超出限制',
	'_ERR_MENU_SUB_BTN_INVALID' => '5008:子菜单数不能超过 {$max} 个',
	'_ERR_MENU_MAIN_BTN_INVALID' => '5009:主菜单数不能超过 {$max} 个',
	'_ERR_MENU_TYPE_INVALID' => '5010:[{$type}]未定义的响应动作',
	'_ERR_MENU_NAME_LENGTH_INVALID' => '5011:[{$name}]菜单名称长度不符',
	'_ERR_MENU_URL_UNDEFINED' => '5012:[{$name}] URL 未定义',
	'_ERR_MENU_KEY_UNDEFINED' => '5013:[{$name}] key 未定义',
	'_ERR_MENU_BYTE_LENGTH_INVALID' => '5014:长度应该介于 {$min} 到 {$max} 字节之间',
	'_ERR_MENU_CHAR_LENGTH_INVALID' => '5015:长度应该介于 {$min} 到 {$max} 个字符之间',
	'_ERR_MENU_INVALID' => '5016:菜单信息错误',
	'_ERR_API_REQUEST_FAILED' => '5017:应用ID填写错误',
	'_ERR_API_NO_ADDRBOOK_PERMISSION' => '5018:畅移没有权限同步, 请到微信企业号开启通讯录权限',
	'_ERR_WX_SERVER_BUSY' => '5019:服务器繁忙',
	'_ERR_WX_DEPARTMENT_DUPLICATE' => '5020:部门名称不能重复 [{$name}]',
	'_ERR_API_USERID_IS_EXIST' => '5021:用户 UserID 已存在 [{$name}]',
	'_ERR_WX_MSG_IS_EMPTY' => '5022:消息为空',

	// 用户不存在, 请同步
	'PLEASE_RSYNC_MEMBER' => '5023:请联系您公司管理员，登录畅移后台进行人员【同步】',
	'PLEASE_RSYNC_MEMBER_TITLE' => '5024:尚未加入企业',
	'PLEASE_LOGIN' => '5025:请先登录',

	// 应用相关
	'_ERR_PLUGIN_IS_LOST' => '5026:应用信息丢失，请重新开启',
	'_ERR_PLUGIN_IS_CLOSED_OR_UNOPEN' => '5027:本应用尚未开启 或 已关闭，请联系管理员启用后使用',
	'_ERR_PLUGIN_NAME_IS_EMPTY' => '5028:应用唯一标识不能为空',

	'_ERR_AUTH_URL_INVALID' => '5029:重定向授权URL参数不能为空',
	'PLEASE_AUTH_WECHAT' => '5030:请先进行微信授权操作',

	// 文件上传 5050-5100
	'_ERR_FILE_UP' => '5050:上传文件发生未知错误',
	// 附件ID错误
	'_ERR_SERVICEID_NOT_EXISTS' => '5051:serverid不能为空',

	// 数据操作参数错误
	'_ERR_D_PARAMS_ERROR' => '5101:参数错误',
	'_ERR_SUITE_TOKEN_ERROR' => '5102:套件token错误',
	'_ERR_CACHE_UNDEFINED' => '5103:未定义缓存'

);
