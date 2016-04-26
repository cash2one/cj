<?php
/**
 * 签到错误
 * $Author$
 * $Id$
 */

class voa_errcode_api_sign {
	const SIGN_FAILED = '21000:%s失败, 请重新进行操作';
	const DATE_RANGE_ERROR = '21001:时间范围错误';
	const SIGN_DUPLICATE = '21001:已经签到, 不能重复签';
	const REASON_NO_NULL = '21002:备注不能为空';
	const NO_PRIVILEGE = '21003:请签到/签退后再进行备注操作';
	const EDIT_FAIL = '21004:修改备注操作失败';
	const INSERT_FAIL = '21005:添加备注操作失败';
	const REASON_TOO_LONG = '21006:备注内容不能超过 %s个字';
	const NO_PERMISSIONS = '21007:没有权限';
}
