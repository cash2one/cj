<?php
/**
 * map
 * 调用地图方法
 *
 * $Author$
 * $Id$
 */

class map {

	/** 错误编码 */
	public $errcode = 0;
	/** 错误详情 */
	public $errmsg = '';

	/** 百度地图纠偏接口 URL配置*/
	const BAIDU_COORD_URL = 'http://api.map.baidu.com/ag/coord/convert?from=0&to=4&x=%s&y=%s';
	/** 百度地图反地址解析接口 URL配置*/
	const BAIDU_LOCATION_URL = 'http://api.map.baidu.com/geocoder?coord_type=gcj02&output=json&location=%s';

	/** 腾讯地图 逆地址解析（坐标描述）接口 http://lbs.qq.com/webservice_v1/guide-gcoder.html */
	const QQ_GEOCODER_URL = 'http://apis.map.qq.com/ws/geocoder/v1?location=%s,%s&coord_type=%s&get_poi=%s&key=%s&output=json';

	/** 腾讯地图Key from qq=6458335 */
	const QQ_MAP_KEY = 'XAWBZ-QVXWR-N2HWC-WR3BG-QU7O6-K7BIL';

	/**
	 * 纠正经纬偏移量
	 * @param  mixed $url
	 * @return void
	 */
	private function _get_map_coord($url) {
		// 使用 snoopy 进行发送
		$snoopy = new snoopy();
		$result = $snoopy->fetch($url);

		// 网络返回结果判断
		if ($result === false) {
			$this->errcode = '10001';
			$this->errmsg = '偏移纠正处网络错误';
			logger::error($this->errcode."\t".$this->errmsg."\t".$url);
			return false;
		}

		// josn 解析判断
		$results = json_decode($snoopy->results, true);
		if ($results === null) {
			$this->errcode = '10002';
			$this->errmsg = '偏移纠正处json解析错误';
			logger::error($this->errcode."\t".$this->errmsg."\t".$url."\t".$snoopy->results);
			return false;
		}
		return  $results;
	}

	/**
	 * 反地址解析获得地理位置
	 * @param  mixed $url
	 * @return void
	 */
	private function _get_map_latlng($url) {
		//获得纠偏数据
		$latlng = $this->_get_map_coord($url);

		// 返回数据检测
		if ($latlng === false) {
			return false;
		}
		if (!isset($latlng['error'])) {
			$this->errcode = '10003';
			$this->errmsg = '偏移纠正返回数据错误';
			logger::error($this->errcode."\t".$this->errmsg."\t".print_r($latlng, true));
			return false;
		}
		// 错误判断
		if ($latlng['error']) {
			$this->errcode = '10004';
			$this->errmsg = '偏移纠正错误(error:'.$latlng['error'].')';
			logger::error($this->errcode."\t".$this->errmsg."\t".$url."\t".print_r($latlng, true));
			return false;
		}
		// 使用 snoopy 进行发送
		$snoopy = new snoopy();

		// 反地址解析参数
		if (!isset($latlng['x']) || !isset($latlng['y'])) {
			$this->errcode = '10005';
			$this->errmsg = '返回纠偏数据经纬度缺少';
			logger::error($this->errcode."\t".$this->errmsg."\t".$url."\t".print_r($latlng, true));
			return false;
		}
		$location = base64_decode($latlng['y']).','.base64_decode($latlng['x']);
		$url = sprintf(self::BAIDU_LOCATION_URL, $location);

		// snoopy fetch 方法
		$result = $snoopy->fetch($url);
		// 网络返回结果判断
		if ($result === false) {
			$this->errcode = '10006';
			$this->errmsg = '返地址解析处网络错误';
			logger::error($this->errcode."\t".$this->errmsg."\t".$url);
			return false;
		}

		// josn 解析判断
		$results = json_decode($snoopy->results, true);
		if ($results === null) {
			$this->errcode = '10007';
			$this->errmsg = '返地址解析处json解析错误';
			logger::error($this->errcode."\t".$this->errmsg."\t".$url."\t".$snoopy->results);
			return false;
		}

		// 返回结果状态判断
		if (!isset($results['status'])) {
			$this->errcode = '10008';
			$this->errmsg = '反地址解析状态缺少';
			logger::error($this->errcode."\t".$this->errmsg."\t".$url."\t".print_r($results, true));
			return false;
		}

		if (strtolower($results['status']) != 'ok') {
			$this->errcode = '10009';
			$this->errmsg = '反地址解析错误';
			logger::error($this->errcode."\t".$this->errmsg."\t".$url."\t".print_r($result, true));
			return false;
		}

		// 构造统一的输出
		$results = array(
			'address' => isset($results['result']['formatted_address']) ? $results['result']['formatted_address'] : '',
			'source' => $results
		);

		return $results;
	}

	/**
	 * 获得地理位置（百度地图接口）
	 * @param unknown $lng 经度
	 * @param unknown $lat 纬度
	 */
	private function _get_map_baidu($lng, $lat) {
		if (!isset($lat) || !isset($lng)) {
			$this->errcode = '10010';
			$this->errmsg = '经纬度参数缺少';
			return false;
		}
		$url = sprintf(self::BAIDU_COORD_URL, $lng, $lat);
		return $this->_get_map_latlng($url);
	}

	/**
	 * 获得地理位置（腾讯地图接口）
	 * @param float $lng 经度
	 * @param float $lat 纬度
	 * @return string
	 */
	private function _get_map_qq($lng, $lat, $coord_type = 1) {

		$get_poi = 0;
		$url = sprintf(self::QQ_GEOCODER_URL, $lat, $lng, $coord_type, $get_poi, self::QQ_MAP_KEY);

		// 使用 snoopy 进行发送
		$snoopy = new snoopy();
		// snoopy fetch 方法
		$result = $snoopy->fetch($url);
		// 网络返回结果判断
		if ($result === false) {
			$this->errcode = '10101';
			$this->errmsg = '读取逆地址解析错误';
			logger::error($this->errcode."\t".$this->errmsg."\t".$url);
			return false;
		}

		// josn 解析判断
		$results = json_decode($snoopy->results, true);
		if ($results === null) {
			$this->errcode = '10102';
			$this->errmsg = '逆地址解析读取json解析错误';
			logger::error($this->errcode."\t".$this->errmsg."\t".$url."\t".$snoopy->results);
			return false;
		}

		// 返回结果状态判断
		if (!isset($results['status'])) {
			$this->errcode = '10103';
			$this->errmsg = '逆地址解析返回状态错误';
			logger::error($this->errcode."\t".$this->errmsg."\t".$url."\t".print_r($results, true));
			return false;
		}

		if ($results['status'] != 0) {
			$this->errcode = '10104';
			$this->errmsg = '解析地理位置数据出错: '.$results['message'];
			logger::error($this->errcode."\t".$this->errmsg."\t".$url."\t".print_r($result, true));
			return false;
		}

		// 构造统一的输出
		$results = array(
			'address' => isset($results['result']['formatted_address']) ? $results['result']['formatted_address'] : $results['result']['address'],
			'source' => $results
		);

		return $results;
	}

	/**
	 * 获取地理位置信息
	 * @param unknown $lng 经度
	 * @param unknown $lat 纬度
	 * @param string $type
	 * @return array|false
	 * + address
	 * + source
	 */
	public function get_map($lng, $lat, $type='qq') {
		switch ($type) {
			case 'baidu':
				return  $this->_get_map_baidu($lng, $lat);
				break;
			case 'sogou':
				return  $this->_get_map_sogou($lng, $lat);
				break;
			case 'qq':
				return $this->_get_map_qq($lng, $lat);
				break;
			default:
				return  $this->_get_map_baidu($lng, $lat);
		}

	}

}
