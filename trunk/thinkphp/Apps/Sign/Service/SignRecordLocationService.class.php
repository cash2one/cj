<?php
/**
 * SignRecordLocationService.class.php
 * $author$
 */

namespace Sign\Service;

class SignRecordLocationService extends AbstractService {

	// 构造方法
	public function __construct() {
		$this->_d = D("Sign/SignRecordLocation");
		parent::__construct();

	}

	/**
	 * 根据经纬度获取数据
	 * @param $lng 经度
	 * @param $lat 纬度
	 * @return bool
	 */
	public function get_location($lng, $lat) {

		// 四舍五入 到 5位
		$lng = round($lng, 5);
		$lat = round($lat, 5);

		return $this->_d->get_location($lng, $lat);
	}

	/**
	 * 存储数据
	 * @param $lng 经度
	 * @param $lat 纬度
	 * @param $address 地址
	 * @return bool
	 */
	public function insert_location($lng, $lat, $address) {

		$data = array(
			'longitude' => $lng,
			'latitude' => $lat,
			'address' => $address
		);
		return $this->_d->insert($data);
	}


}
