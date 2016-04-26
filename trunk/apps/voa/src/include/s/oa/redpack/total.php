<?php
/**
 * total.php
 * 红包统计数据 service
 * $Author$
 * $Id$
 */

class voa_s_oa_redpack_total extends voa_s_abstract {

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
	 * @param array $total
	 * @return array
	 */
	public function format(array &$total) {

		$total['_money'] = number_format($total['money'] / 100, 2);
		// 时间字段
		$time_fields = array('created', 'updated', 'deleted');
		foreach ($time_fields as $_key) {
			$total['_' . $_key] = rgmdate($total[$_key], 'Y-m-d H:i');
		}

		return $total;
	}

}
