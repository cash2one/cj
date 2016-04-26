<?php
/**
 * redpack.php
 * 红包主表service
 * $Author$
 * $Id$
 */

class voa_s_oa_redpack extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 获取红包信息
	 *
	 * @param number $id 红包ID
	 * @return array
	 */
	public function redpack($id) {

		$redpack = $this->get($id);
		if (empty($redpack)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_redpack::NOT_EXISTS, $id);
		}

		// 红包分配规则
		$redpack['rule'] = @unserialize($redpack['rule']);
		return $redpack;
	}

	/**
	 * 判断红包活动是否到期
	 *
	 * @param array $redpack 红包信息
	 * @return boolean
	 */
	public function is_end(array $redpack) {

		if (! empty($redpack['endtime']) && startup_env::get('timestamp') > $redpack['endtime']) {
			return true;
		}

		return false;
	}

	/**
	 * 判断红包活动是否已开始
	 *
	 * @param array $redpack
	 * @return boolean
	 */
	public function is_start(array $redpack) {

		if (! empty($redpack['starttime']) && startup_env::get('timestamp') < $redpack['starttime']) {
			return false;
		}

		return true;
	}

	/**
	 * 格式化红包信息
	 *
	 * @param array $redpack
	 * @return array
	 */
	public function format(array &$redpack) {

		// 时间字段
		$time_fields = array('starttime', 'endtime', 'created', 'updated', 'deleted');
		foreach ($time_fields as $_key) {
			$redpack['_' . $_key] = rgmdate($redpack[$_key], 'Y-m-d H:i');
		}

		if (voa_d_oa_redpack::TYPE_RAND == $redpack['type']) {
			$redpack['_type'] = '随机';
		} else {
			$redpack['_type'] = '平均';
		}

		$redpack['_total'] = number_format($redpack['total'] / 100, 2);
		$redpack['_left'] = number_format($redpack['left'] / 100, 2);

		return $redpack;
	}

}
