<?php
/**
 * voa_errcode_oa_thread
 * OA thread 相关 呼叫错误码
 * 使用7位编码
 *
 * $Author$
 * $Id$
 */

class voa_errcode_oa_thread {

	const SUBJECT_IS_EMPTY = '1001000:标题不能为空';
	const MESSAGE_IS_EMPTY = '1001001:内容不能为空';
	const THREAD_IS_NOT_EXISTS = '1001002:帖子不存在或已被删除';
	const NO_PRIVILEGE = '1001003:没有权限';
	const REPLY_IS_NOT_EXISTS = '1001004:评论信息不存在';

}
