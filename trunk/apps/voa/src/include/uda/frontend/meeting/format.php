<?php
/**
 * 会议数据格式化
 * $Author$
 * $Id$
 */

class voa_uda_frontend_meeting_format extends voa_uda_frontend_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化会议信息
	 * @param array $meeting
	 */
	public function meeting(&$meeting) {
		$meeting['mt_subject'] = rhtmlspecialchars($meeting['mt_subject']);
		$meeting['mt_message'] = rhtmlspecialchars($meeting['mt_message']);
		$meeting['_begintime'] = rgmdate($meeting['mt_begintime'], 'Y-m-d H:i');
		$meeting['_created'] = rgmdate($meeting['mt_created'], 'Y-m-d H:i');
		$meeting['_created_u'] = rgmdate($meeting['mt_created']);
		return true;
	}
}
