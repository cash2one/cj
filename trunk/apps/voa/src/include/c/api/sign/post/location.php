<?php

/**
 * location.php
 * 上报地理位置接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_sign_post_location extends voa_c_api_sign_base {

	public function execute() {

		// 接受的参数
		$fields = array (
			'longitude' => array ('type' => 'string', 'required' => true),// 经度
			'latitude' => array ('type' => 'string', 'required' => true),// 纬度
			'precision' => array ('type' => 'string', 'required' => true)// 精度
		);

		// 基本变量检查
		if (!$this->_check_params($fields)) {
			return false;
		}
		

		// 获取当前人员最后一次地理位置上报信息
		$last = $this->_serv_sign_location->last_by_uid(startup_env::get('wbs_uid'), 0, 1);
		if (!empty($last) && ($diff = startup_env::get('timestamp') - $last['sl_signtime']) < $this->_p_sets['up_position_rate']) {
			return $this->_set_errcode(voa_errcode_oa_sign::LOCATION_TOO_SHORT, ceil(($diff + 1) / $this->_p_sets['up_position_rate']));
		}

		// 获取经纬度
		$this->__get_jingwei($last, $new_location);

		// 获取位置信息
		$location = array ();
		if (!$this->_get_address($this->_params['longitude'], $this->_params['latitude'], $location)) {
			$this->_errcode = 0;
			$this->_errmsg = 'ok';
			$this->__get_address_by_ip($location);
		}

		// 待插入的位置上报数据
		$insert = array (
			'm_uid' => $this->_member['m_uid'],
			'm_username' => $this->_member['m_username'],
			'sl_signtime' => startup_env::get('timestamp'),
			'sl_ip' => controller_request::get_instance()->get_client_ip(),
			'sl_longitude' => $location['longitude'],
			'sl_latitude' => $location['latitude'],
			'sl_address' => $location['address']
		);

		// 新增地理位置上报记录
		$result = $this->_serv_sign_location->insert($insert, true);
		if (empty($result)) {
			return $this->_set_errcode(voa_errcode_oa_sign::LOCATION_SUBMIT_FAILED);
		}

		// 新增微信公共地理位置数据
		if ($new_location && $this->_params['precision']) {
			$this->_serv_weixin_location->insert(array (
				'm_uid' => $this->_member['m_uid'],
				'm_username' => $this->_member['m_username'],
				'wl_latitude' => $location['latitude'],
				'wl_longitude' => $location['longitude'],
				'wl_precision' => $this->_params['precision'],
				'wl_ip' => controller_request::get_instance()->get_client_ip()
			));
		}

		// 返回结果
		$this->_result = array (
			'time' => rgmdate(startup_env::get('timestamp'), 'H:i'),
			'address' => $location['address']
		);
	}

	/**
	 * 获取经纬度
	 * @param $last
	 * @param $new_location
	 * @return bool|void
	 */
	private function __get_jingwei($last, &$new_location) {
		$new_location = true;
		if (!$this->_get_location($this->_params['longitude'], $this->_params['latitude'])) {
			// 获取失败尝试读取上次上报的位置信息
			if (!empty($last)) {
				if (startup_env::get('timestamp') - $last['sl_created'] > 3600) {
					return $this->_set_errcode(voa_errcode_oa_sign::LOCATION_EXPIRED);
				}
				$this->_params['longitude'] = $last['sl_longitude'];
				$this->_params['latitude'] = $last['sl_latitude'];
				$new_location = false;
			}
			unset($last);
		}

		return true;
	}

	/**
	 * 验证数据
	 * @return bool
	 */
	private function __execute() {
		// 接受的参数
		$fields = array (
			'longitude' => array ('type' => 'string', 'required' => true),// 经度
			'latitude' => array ('type' => 'string', 'required' => true),// 纬度
			'precision' => array ('type' => 'string', 'required' => true)// 精度
		);

		// 基本变量检查
		if (!$this->_check_params($fields)) {
			return false;
		}

		return true;
	}

	/**
	 * 通过IP地址获取位置信息
	 * @param array $location
	 * @return boolean
	 */
	private function __get_address_by_ip(array &$location) {

		$ip = controller_request::get_instance()->get_client_ip();
		$ip2address = new ip2address();
		if (!$ip2address->get($ip)) {
			$ip2address->result['address'] = '无法获取地理位置';
			//$this->_errcode = $ip2address->errcode;
			//$this->_errmsg = $ip2address->errmsg;
			//return false;
		}
		
		$location = array (
			'longitude' => 0,
			'latitude' => 0,
			'address' => $ip2address->result['address']
		);

		return true;
	}

}
