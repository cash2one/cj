<?php
/**
 * mem.php
 * 红包人员权限 service
 * $Author$
 * $Id$
 */

class voa_s_oa_redpack_mem extends voa_s_abstract {

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
	 * @param array $mem
	 * @return array
	 */
	public function format(array &$mem) {

		// 时间字段
		$time_fields = array('created', 'updated', 'deleted');
		foreach ($time_fields as $_key) {
			$mem['_' . $_key] = rgmdate($mem[$_key], 'Y-m-d H:i');
		}

		return $mem;
	}

}
