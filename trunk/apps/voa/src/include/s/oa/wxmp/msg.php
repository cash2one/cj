<?php
/**
 * 公众号消息
 * $Author$
 * $Id$
 */

class voa_s_oa_wxmp_msg extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 格式化评分项数据
	 * @param array &$msg 评分信息
	 * @return boolean
	 */
	public function format(&$msg) {

		// 发起时间
		$msg['_created'] = rgmdate($msg['created'], 'Y-m-d H:i');
		$msg['_updated'] = rgmdate($msg['updated'], 'Y-m-d H:i');
		list($msg['_created_ymd'], $msg['_created_hi']) = explode(' ', $msg['_created']);
		// 个性化发起时间
		$msg['_updated_u'] = rgmdate($msg['updated'], 'u');

		return true;
	}

}

