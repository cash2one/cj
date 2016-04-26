<?php

/**
 * voa_uda_frontend_sign_format
 * 统一数据访问/任务应用/数据格式化
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_sign_format extends voa_uda_frontend_sign_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化投票数据数组
	 * @param array $list 投票信息数组
	 * @return boolean
	 */
	public function sign_record_list(&$list) {
		foreach ($list as &$sign) {
			$this->sign_record($sign);
		}

		return true;
	}

	/**
	 * 格式化投票数据
	 * @param $sign
	 * @return bool
	 */
	public function sign_record(&$sign) {
		$sign['_updated'] = rgmdate($sign['sr_updated'], 'Y-m-d H:i');
		$sign['_signtime'] = rgmdate($sign['sr_signtime'], 'Y-m-d H:i');
		$sign['_signtime_hi'] = rgmdate($sign['sr_signtime'], 'H:i');

		return true;
	}

	/**
	 * 格式化地理位置数据数组
	 * @param array $list 地理位置数组
	 * @return boolean
	 */
	public function sign_location_list(&$list) {
		foreach ($list as &$sign) {
			$this->sign_location($sign);
		}

		return true;
	}

	/**
	 * 格式化地理位置数据
	 * @param $sign
	 * @return bool
	 */
	public function sign_location(&$sign) {
		$sign['_updated'] = rgmdate($sign['sl_updated'], 'Y-m-d H:i');
		$sign['_signtime'] = rgmdate($sign['sl_signtime'], 'Y-m-d H:i');
		$sign['_signtime_hi'] = rgmdate($sign['sl_signtime'], 'H:i');

		return true;
	}
}
