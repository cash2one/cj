<?php
/**
 * 巡店任务表
 * $Author$
 * $Id$
 */

class voa_s_oa_inspect_tasks extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 格式化巡店任务数据
	 * @param array &$task 巡店信息
	 * @return boolean
	 */
	public function format(&$task) {

		// 发起时间
		$task['_created'] = rgmdate($task['it_created'], 'Y-m-d H:i');
		list($task['_created_ymd'], $task['_created_hi']) = explode(' ', $task['_created']);
		// 个性化发起时间
		$task['_updated_u'] = rgmdate($task['it_updated'], 'u');

		return true;
	}

}
