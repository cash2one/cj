<?php
/**
 * setting.php
 * 红包配置 service
 * $Author$
 * $Id$
 */

class voa_s_oa_redpack_setting extends voa_s_abstract {

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
	 * @param array $setting
	 * @return array
	 */
	public function format(array &$setting) {

		// 时间字段
		$time_fields = array('created', 'updated', 'deleted');
		foreach ($time_fields as $_key) {
			$setting['_' . $_key] = rgmdate($setting[$_key], 'Y-m-d H:i');
		}

		return $setting;
	}

}
