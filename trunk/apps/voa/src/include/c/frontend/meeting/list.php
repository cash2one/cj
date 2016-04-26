<?php
/**
 * 会议列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_meeting_list extends voa_c_frontend_meeting_base {
	/** 分页查询相关 */
	protected $_start;
	protected $_perpage;
	protected $_page;
	/** 更新时间 */
	protected $_updated;

	public function __construct() {
		parent::__construct();
	}

	public function execute() {

		$this->_output('meeting/list');
	}
}
