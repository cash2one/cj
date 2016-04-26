<?php
/**
 * voa_s_oa_travel_diyindex
 * 素材
 *
 * $Author$
 * $Id$
 */

class voa_s_oa_travel_diyindex extends voa_s_abstract {


	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 格式化素材信息
	 * @param array $data 素材信息
	 * @return boolean
	 */
	public function format(&$data) {

		$data['_created'] = rgmdate($data['created']);
		$data['_updated'] = rgmdate($data['updated']);

		// 解析首页内容
		$data['_message'] = unserialize($data['message']);

		return true;
	}

	/**
	 * 检查标题
	 * @param string $subject 标题
	 * @param string $err
	 */
	public function chk_subject($subject, $err) {

		if (empty($subject)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_travel::DIYINDEX_SUBJECT_IS_EMPTY);
			return false;
		}

		return true;
	}

	/**
	 * 检查内容
	 * @param array $message 内容
	 * @param string $err
	 */
	public function chk_message($message, $err) {

		if (empty($message)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_travel::DIYINDEX_MESSAGE_IS_EMPTY);
			return false;
		}

		return true;
	}

}
