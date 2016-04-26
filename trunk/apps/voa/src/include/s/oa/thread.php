<?php
/**
 * 日志/记录主题表
 * $Author$
 * $Id$
 */

class voa_s_oa_thread extends voa_s_abstract {

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
	public function format(&$thread) {

		$thread['_created'] = rgmdate($thread['created'], 'u');
		$thread['_updated'] = rgmdate($thread['updated'], 'u');
		$thread['_subject'] = nl2br(rhtmlspecialchars($thread['subject']));

		return true;
	}

	public function chk_subject($subject, $err = '') {

		if (empty($subject)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_thread::SUBJECT_IS_EMPTY);
			return false;
		}

		return true;
	}

	public function chk_message($message, $err = '') {

		if (empty($message)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_thread::MESSAGE_IS_EMPTY);
			return false;
		}

		return true;
	}

	public function chk_uids(&$uidstr, $err = '') {

		// 获取分享用户 uid
		$uids = array();
		$get_uids = explode(',', $uidstr);
		foreach ($get_uids as $k => $v) {
			$v = intval($v);
			if ($v && $v != startup_env::get('wbs_uid')) {
				$uids[$v] = $v;
			}
		}

		$uidstr = implode(",", $uids);

		return true;
	}

}
