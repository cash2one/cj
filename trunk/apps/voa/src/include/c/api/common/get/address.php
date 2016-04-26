<?php
/**
 * address.php
 * 获取给定经纬度所在的地理位置信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_common_get_address extends voa_c_api_common_abstract {

	protected function _before_action($action) {
		if (!empty($_SERVER['HTTP_HOST']) && stripos($_SERVER['HTTP_HOST'], 'local.vchangyi.net') !== false) {
			$this->_require_login = false;
		}
		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

		// 需要传入的参数
		$fields = array(
			// 经度
			'longitude' => array('type' => 'string_trim', 'required' => true),
			// 纬度
			'latitude' => array('type' => 'string_trim', 'required' => true),
		);

		// 参数数值基本检查验证
		if (!$this->_check_params($fields)) {
			return false;
		}

		$map = new map();
		$result = $map->get_map($this->_params['longitude'], $this->_params['latitude']);
		if (!$result) {
			$this->_errcode = $map->errcode;
			$this->_errmsg = $map->errmsg;
			return false;
		}

		$this->_result = array(
			'address' => $result['address']
		);

		return true;
	}
}

