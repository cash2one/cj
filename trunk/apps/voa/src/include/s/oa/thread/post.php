<?php
/**
 * 日志/记录详细信息表
 * $Author$
 * $Id$
 */

class voa_s_oa_thread_post extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 格式化帖子信息
	 * @param array $thread 帖子信息
	 * @return boolean
	 */
	public function format(&$post) {
		$post['_created'] = rgmdate($post['created'], 'u');
		$post['_updated'] = rgmdate($post['updated'], 'u');
		$post['_message'] = nl2br(rhtmlspecialchars($post['message']));

		return true;
	}

	public function chk_message($message, $err = '') {

		if (empty($message)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_thread::MESSAGE_IS_EMPTY);
			return false;
		}

		return true;
	}

}
