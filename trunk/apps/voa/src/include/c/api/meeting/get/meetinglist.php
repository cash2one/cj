<?php
/**
 * voa_c_api_meeting_get_meetinglist
 * 会议室列表
 * $Author$
 * $Id$
 */
class voa_c_api_meeting_get_meetinglist extends voa_c_api_meeting_base {


	public function execute() {

		// 输出结果
		$this->_result = array(
			'data' => $this->_rooms ? array_values($this->_rooms) : array()
		);

		return true;
	}

}
