<?php
/**
 * address.php
 * 通过经纬度获取地理位置信息
 *<code>
 * http://test.vchangyi.com/api/map/get/list/?lat=39.990912172420714&lng=116.32715863448607
 *</code>
 * $Author$
 * $Id$
 */
class voa_c_api_map_get_list extends voa_c_api_map_base {

	public function execute() {
		//请求参数
		$fields = array(
			//纬度
			'lat' => array('type' => 'string', 'required' => true),
			//经度
			'lng' => array('type' => 'string', 'required' => true),
		);
		//检查参数
		if (!$this->_check_params($fields)) {
			return false;
		}
		//返回结果
		$maps = new map();
		$result = $maps->get_map($this->_params['lng'] ,$this->_params['lat']);

		// map 返回错语
		if ($result === false) {
			$this->_set_errcode($maps->errmsg);
			return false;
		}
		// 重组返回json数组
		$this->_result = array(
			'address' => $result['address'],
		);

		return true;

	}

}

