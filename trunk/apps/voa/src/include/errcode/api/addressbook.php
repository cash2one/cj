<?php
/**
 * voa_errcode_uc_addressbook
 * API 接口通信录错误码定义
 * 约定错误码均以1开头的5位数字
 * 每个错误码以常量定义，格式为“errcode:errmsg”
 * errcode为唯一的整型
 * errmsg为错误信息，可使用%s做为变量
 * 一旦提供文档，错误码errcode不允许变更！！
 * Create By Mojianyuan
 * $Author$
 * $Id$
 */
class voa_errcode_api_addressbook {

	const UPDATE_ERROR = '80001:更新出错';
	const ID_EMPTY_ERROR = '80002:ID值不能为空';
	const ITEM_EMPTY_ERROR = '80003:指定的通讯录记录不存在 或 已被删除';
	/** 密码必须使用 32位的 md5 格式 */
	const NEWPW_FORMAT_ERROR = '80004:密码格式错误';
	const ADDRESSBOOK_SHARE_FAILED = '80004:通讯录分享失败';
	/** 无法自cookie信息读取用户身份 */
	const USER_NOT_LOGIN = '80005:非法的用户身份';
	/** 需要对旧密码原文进行32位的md5 */
	const PW_NOT_MD5 = '80006:旧密码传输格式不正确';
	/** 需要对新密码原文进行32位md5 */
	const NEWPW_NOT_MD5 = '80007:新密码传输格式不正确';
	/** 密码未变动 */
	const PW_IS_SAME = '80008:密码未变更不需要提交修改';

}
