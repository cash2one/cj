<?php
/**
 * 接收来自微信的消息
 * $Author$
 * $Id$
 */

class voa_wxlife_service extends voa_wxlife_base {

	static function &instance() {
		static $object;
		if(empty($object)) {
			$object	= new self();
		}

		return $object;
	}

	public function __construct() {
		parent::__construct();
	}

	/** 获取用户信息 */
	public function get_user() {
		return parent::get_user();
	}

	/** 发送模板消息 */
	public function send_msg($openid, $tplid, $data) {
		return parent::send_msg($openid, $tplid, $data);
	}
}
