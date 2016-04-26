<?php
/**
 * location.php
 * 所在地理位置相关的公共方法
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_h_location {

	public static $errcode = 0;
	public static $errmsg = '';
	private static $_ip2address = null;
	private static $_map = null;

	/**
	 * 获取指定人员的地理位置
	 * @param number $uid m_uid
	 * @param string $ip IP地址，不指定则使用当前IP
	 * @param number $longlat_expire 经纬度有效期，单位:秒
	 * @param string $get_address 是否获取经纬度。true=获取(默认),false=不获取
	 * @return array
	 * + ip IP
	 * + long 经度
	 * + lat 纬度
	 * + address 所在地理位置
	 */
	public static function get_address($uid = 0, $ip = null, $longlat_expire = 900, $get_address = true) {

		// 未提供IP则取当前IP
		if (!$ip) {
			$ip = controller_request::get_instance()->get_client_ip();
		}
		// 获取经纬度有效期内的记录
		list($long, $lat) = self::get_longlat($uid, $longlat_expire);
		// 地理位置
		$address = '';
		// 需要获取位置
		if ($get_address) {

			// 如果存在经纬度
			if ($long != 0 && $lat != 0) {
				$address = self::get_longlat_address($long, $lat);
			}
			// 经纬度无法获取，则使用IP
			if (!$address) {
				$address = self::get_ip_address($ip);
			}
		}

		return array('ip' => $ip, 'long' => $long, 'lat' => $lat, 'address' => $address);
	}

	/**
	 * 获取指定人员的经纬度
	 * @param number $m_uid
	 * @param number $expire 经纬度有效期
	 * @return array(long, lat)
	 */
	public static function get_longlat($uid, $expire = 900) {

		// 经度
		$long = 0;
		// 纬度
		$lat = 0;
		// 读取统一的微信企业号经纬度集合表
		$serv = &service::factory('voa_s_oa_weixin_location');
		$last = $serv->last_by_uid($uid);
		if (empty($last)) {
			self::$errcode = 1101;
			self::$errmsg = '经纬度信息未上报';
		} elseif (startup_env::get('timestamp') - $last['wl_created'] > $expire) {
			self::$errcode = 1102;
			self::$errmsg = '经纬度信息已过期';
		} else {
			self::$errcode = 0;
			self::$errmsg = '';
			$long = $last['wl_longitude'];
			$lat = $last['wl_latitude'];
		}

		return array($long, $lat);
	}

	/**
	 * 根据IP计算地理位置
	 * @param string $ip
	 * @return string
	 */
	public static function get_ip_address($ip = '') {

		$address = '';
		if (self::$_ip2address === null) {
			self::$_ip2address = new ip2address();
		}
		if (!self::$_ip2address->get($ip)) {
			self::$errcode = self::$_ip2address->errcode;
			self::$errmsg = self::$_ip2address->errmsg;
		} else {
			self::$errcode = 0;
			self::$errmsg = '';
			$address = self::$_ip2address->result['address'];
		}

		return $address;
	}

	/**
	 * 根据经纬度获取地理位置
	 * @param number $long
	 * @param number $lat
	 * @return string
	 */
	public static function get_longlat_address($long, $lat) {

		$address = '';
		if (self::$_map === null) {
			self::$_map = new map();
		}
		$result = self::$_map->get_map($long, $lat);
		if (self::$_map->errcode || !$result) {
			self::$errcode = self::$_map->errcode;
			self::$errmsg = self::$_map->errmsg;
		} else {
			self::$errcode = 0;
			self::$errmsg = '';
			$address = $result['address'];
		}

		return $address;
	}

}
