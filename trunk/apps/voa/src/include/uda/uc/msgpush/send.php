<?php
/**
 * voa_uda_uc_msgpush_send
 * 统一数据访问/邮件发送操作/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_uda_uc_msgpush_send extends voa_uda_uc_msgpush_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 发送消息给用户
	 * @param string $openid 用户唯一标识
	 * @param string $msg
	 */
	public function send($openid, $msg) {

	}

	/**
	 * 发送消息给部门
	 * @param array $parts
	 * @param string $msg
	 */
	public function send_to_department($parts, $msg) {

	}

}
