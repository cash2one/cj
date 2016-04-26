<?php
/**
 * zh-cn.php
 * 群聊的语言文件
 * $Author$
 * $Id$
 */

return array(
	'_ERR_CHATGROUP_IS_NOT_EXIST' => '390000:群组人员不能为空',

	'CHATGROUP_NAME' => '群聊',
	'ONE_CHATGROUP_NAME' => '单聊',

	'ATTACHMENT'=>'附件',
	'CONTENT'=>'文字',
	'IMAGE'=>'图片',

	// 聊天组1-2999
	'_ERR_GROUP_MESSAGE' => '350000:聊天组不能为空',
	'_ERR_EDIT_GROUP_MESSAGE' => '350001:编辑聊天组出错',
	'_ERR_EDIT_GROUP_CGID' => '350002:非法聊天组编号',
	'_ERR_EDIT_GROUP_NOEXIST' => '350003:群组不存在',
	'_ERR_CREATER_CANNOT_QUIT' => '350004:群主不能退出',
	'_ERR_NOT_CREATER_MESSAGE' =>'350005:没有权限',


	//聊天组成员 3000 - 5999
	'_ERR_MEMBER_MESSAGE' => '353000:聊天组成员不能为空',
	'_ERR_REMOVE_MEMBER_MESSAGE' => '353001:聊天组成员移除出错',
	'_ERR_ADD_MEMBER_MESSAGE' => '353002:聊天组成员添加出错',
	'_ERR_NOT_MEMBER_MESSAGE' =>'353003:非聊天组成员',
	'_ERR_RESET_MESSAGE_COUNT' => '353004:重置未读消息数出错',
	'_ERR_EXIT_OR_NOT_IN_CHATGROUP' => '353005:您已退出或不在群里',
	'_ERR_EDIT_MESSAGE_COUNT' => '353006:重置未读消息数出错',


	//聊天消息 6000-9999
	'_ERR_RECORD_ATTACHMENT_MESSAGE' => '356000:发送信息不能为空',
	'_ERR_Add_RECORD_MESSAGE' => '350001:发送聊天信息出错',
	'_ERR_UPDATE_COUNT_ERROR' => '356002:更新未读消息出错',
	'_ERR_MEMBER_MESSAGE_LIMIT' => '356003:每页条数必须在10和50之间',
	'_ERR_MEMBER_MESSAGE_PAGE' => '356004:当前页码不能为空并且必须大于0',
	'_ERR_GET_RECORD_ERROR' => '356005:获取聊天信息出错',
	//用户信息为空
	'_ERR_UID_USERNAME_MESSAGE' => '356006:用户uid或者用户名不能为空',
	//群组信息为空
	'_ERR_CHATGROUP_NAME_ERROR' => '356007:群组名称不能为空',
	//未选择群组人员
	'_ERR_NOT_CHATGROUP_MEMBER_ERROR' => '356008:未选择群组人员',
	'_ERR_RECORD_ID_ERROR' => '356009:聊天记录ID错误',
	'_ERR_RRCORD_MESSAGE_ZERO'=>'356010:消息列表总数为0',
	'_ERR_RRCORD_MESSAGE_NOT_EXIST'=>'356011:消息列表不存在',
);
