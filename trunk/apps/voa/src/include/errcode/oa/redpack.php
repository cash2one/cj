<?php
/**
 * redpack.php
 * 红包错误码
 * $Author$
 * $Id$
 */

class voa_errcode_oa_redpack {
	const NOT_EXISTS = '7001:指定红包（%s）不存在';
	const IS_END = '7002:红包活动 %s 已到期关闭，不能继续参与';
	const NO_START = '7003:指定红包活动尚未开始，请稍候再试';
	const MONEY_OVER = '7004:非常抱歉，红包已发放完毕';
	const UNKNOW_TYPE = '7005:未知的红包发放类型';
	const GOT_LIMIT = '7006:您已领过 %s 次红包，超过活动领取次数限制';
	const MONEY_IS_EMPTY = '7007:红包分配异常，请稍后再试';
}
