<?php

/**
 * 签到操作相关
 * $Author$
 * $Id$
 */
class voa_c_api_sign_base extends voa_c_api_base {

	/**
	 * 签到状态
	 * 1: 出勤
	 * 2: 迟到
	 * 4: 早退
	 * 8: 旷工
	 * 16: 请假
	 * 32: 出差
	 */
	protected $_sign_st = array (
		1 => '出勤',
		2 => '迟到',
		4 => '早退',
		6 => '迟/退',
		8 => '旷工',
		16 => '请假',
		32 => '出差'
	);

	/** 对应样式 */
	protected $_styles = array (
		2 => 'chidao',
		4 => 'zaotui',
		6 => 'chidao',
		8 => 'kuanggong'
	);

	protected $_y;
	protected $_n;
	protected $_year_sel;
	protected $_month_sel;

	/** 地理位置上报数据表 */
	protected $_serv_sign_location = null;
	/** 微信公共地理位置数据表 */
	protected $_serv_weixin_location = null;

	public function __construct() {
		parent::__construct();

		/** 可选年份 */
		$this->_y = rgmdate(startup_env::get('timestamp'), 'Y');
		$this->_n = rgmdate(startup_env::get('timestamp'), 'n');
		$this->_year_sel = array ($this->_y, $this->_y - 1);
		$this->_month_sel = range(0, 11);
		/** 启用详情类的状态值 */
		$this->_sign_st = voa_sign_detail::$s_sign_st;
	}

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.sign.setting', 'oa');
		$this->_serv_sign_location = &service::factory('voa_s_oa_sign_location');
		$this->_serv_weixin_location = &service::factory('voa_s_oa_weixin_location');

		return true;
	}
	
	/**
	 * 获取有效的经纬度
	 * @param float $longitude (引用)经度
	 * @param float $latitude (引用)纬度
	 * @return boolean
	 */
	protected function _get_location(&$longitude, &$latitude) {

		if (!is_numeric($longitude) || !is_numeric($latitude) || !validator::is_in_range($longitude, - 180, 180) || !validator::is_in_range($latitude, - 90, 90)) {

			logger::error("longitude:{$longitude}; latitude:{$latitude}");
			// 自公共位置表获取经纬度，并获取对应的地理位置，获取失败直接显示错误信息
			if ('xxdbnyhdtz.vchangyi.com' == $_SERVER['HTTP_HOST'] || !$this->__location_sign($longitude, $latitude)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * 尝试自公共位置表获取经纬度
	 * @param float (引用) $longitude
	 * @param float (引用) $latitude
	 * @return boolean
	 */
	private function __location_sign(&$longitude, &$latitude) {

		// 最后一次位置信息
		$last = $this->_serv_weixin_location->last_by_uid($this->_member['m_uid']);
		if (empty($last)) {
			return false;
		}

		$longitude = $last['wl_longitude'];
		$latitude = $last['wl_latitude'];

		return true;
	}

	/**
	 * 自给定的经纬度获取位置信息
	 * @param float  $longitude 经度
	 * @param float  $latitude 纬度
	 * @param string $location (应用)地址信息
	 * @return boolean
	 */
	protected function _get_address($longitude, $latitude, &$location) {

		$maps = new map();
		$result = $maps->get_map($longitude, $latitude);
		if ($result === false) {
			// 获取失败
			if ($maps->errmsg) {
				// 返回了错误
				$this->_errcode = $maps->errcode;
				$this->_errmsg = '获取地理位置失败：' . $maps->errmsg;

				return false;
			} else {
				// 未知错误
				$this->_errcode = 101;
				$this->_errmsg = '获取地理位置失败，请稍后再试';

				return false;
			}

			return false;
		}

		if (empty($result['address'])) {
			$this->_errcode = 102;
			$this->_errmsg = '无法解析位置信息，请稍后再试';

			return false;
		}

		$location = array (
			'longitude' => $longitude,
			'latitude' => $latitude,
			'address' => $result['address']
		);

		return true;
	}

	/**
	 * 获取上级部门id
	 * @param unknown $cd_id
	 * @return unknown
	 */
	public function get_upid($cd_id) {
		$deplist = voa_h_cache::get_instance()->get('department', 'oa');
		$upid = $deplist [$cd_id] ['cd_upid'];

		return $upid;
	}

	/**
	 * 格式时间
	 * @param unknown $ymd
	 * @param unknown $num
	 * @return number
	 */
	public function totime($ymd, $num) {
		$time = $num;
		$h = substr($time, 0, 2);
		//2015-08-15 25:23;
		if ($h - 24 > 0) {
			$diff = $h - 24;
			$m = substr($time, 3, 2);
			$formattime = strtotime('+1 day', strtotime($ymd)) + $diff * 3600 + $m * 60 - 8 * 3600;

		}

		return $formattime;
	}

}
