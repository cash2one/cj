<?php

/**
 * voa_uda_frontend_sign_base
 * 统一数据访问/签到应用/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_sign_base extends voa_uda_frontend_base {
	/** 配置信息 */
	protected $_sets = array();
	protected $_sitesets = array();

	public function __construct() {
		parent::__construct();
		$this->_sitesets = voa_h_cache::get_instance()->get('setting', 'oa');
		$this->_sets = voa_h_cache::get_instance()->get('plugin.sign.setting', 'oa');
	}

	/**
	 * 格式化时间
	 * @param $num
	 * @return string
	 */
	public function formattime($num) {
		if (strlen($num) == 0) {
			$time = '00:00';

			return $time;
		} elseif (strlen($num) == 1) {
			$time = '00:0' . $num;

			return $time;
		} elseif (strlen($num) == 2) {
			$time = '00:' . $num;

			return $time;
		} elseif (strlen($num) == 3) {
			$hour = substr($num, 0, 1);
			$min = substr($num, 1, 2);
			$time = '0' . $hour . ':' . $min;

			return $time;
		} else {
			$hour = substr($num, 0, 2);
			$min = substr($num, 2, 2);
			$time = $hour . ':' . $min;

			return $time;
		}
	}

	/**
	 * 格式化数字
	 * @param $num
	 * @return string
	 */
	public function formatnum($num) {
		if (strlen($num) == 0) {
			$time = '0000';

			return $time;
		} elseif (strlen($num) == 1) {
			$time = '000' . $num;

			return $time;
		} elseif (strlen($num) == 2) {
			$time = '00' . $num;

			return $time;
		} elseif (strlen($num) == 3) {
			$hour = substr($num, 0, 1);
			$min = substr($num, 1, 2);
			$time = '0' . $hour . $min;

			return $time;
		} else {
			$hour = substr($num, 0, 2);
			$min = substr($num, 2, 2);
			$time = $hour . $min;

			return $num;
		}
	}
}
