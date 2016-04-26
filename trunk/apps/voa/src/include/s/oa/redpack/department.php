<?php
/**
 * department.php
 * 红包部门权限 service
 * $Author$
 * $Id$
 */

class voa_s_oa_redpack_department extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 格式化信息
	 *
	 * @param array $dp
	 * @return array
	 */
	public function format(array &$dp) {

		// 时间字段
		$time_fields = array('created', 'updated', 'deleted');
		foreach ($time_fields as $_key) {
			$dp['_' . $_key] = rgmdate($dp[$_key], 'Y-m-d H:i');
		}

		return $dp;
	}

}
