<?php
/**
 * @Author: ppker
 * @Date:   2015-09-16 16:40:18
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-09-24 16:11:44
 */

namespace Common\Service;
use Common\Service\AbstractSettingService;

class WeixinLocationService extends AbstractSettingService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Common/WeixinLocation');
	}

	/**
	 * 获取用户的最后一条记录
	 * @param int $uid 用户UID
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_last($uid) {

		return $this->_d->get_last($uid);
	}

	/**
	 * [get_address_by_location 根据地址获取最新的]
	 * @return [type] [description]
	 */
	public function get_address_by_location($location) {

        // 经度
		$longitude = $location['longitude'];
        // 纬度
		$latitude = $location['latitude'];
		$map = new \Com\Location();
		$result = $map->get_address($longitude, $latitude);

		// 进行判断
		if ($result === false) {
			return false;
		}

		if (empty($result['address'])) {
			return false;
		}

		return array(
			'longitude' => $longitude,
			'latitude' => $latitude,
			'address' => $result['address']
		);
	}

	/**
	 * [get_address_by_ip 根据IP获取对应的地理位置信息]
	 * @param array &$location [description]
	 * @return [type] [description]
	 */
	public function get_address_by_ip() {

		$ip = get_client_ip();
		$ip2address = new \Org\Net\Ip2address();
		if (!$ip2address->get($ip)) {
			$ip2address->result['address'] = '无法获取地理位置';
		} else {
			if (!empty($ip2address->errcode)) {
				\Com\Error::instance()->set_error($ip2address->errmsg, $ip2address->errcode);
				return false;
			}
		}

		$location = array(
			'longitude' => 0,
			'latitude' => 0,
			'address' => $ip2address->result['address']
		);

		return $location;
	}

	/**
	 * [insert_weixin 插入微信公共地址信息]
	 * @param [array] $params [传入的参数数据]
	 * @param [array] $extends [扩展数据 m_uid username]
	 * @return [string] [返回插入数据的记录主键ID号]
	 */
	public function insert_weixin($params, $extends) {

		// 生成数据
		$weixin_data = array(
			'm_uid' => $extends['m_uid'],
			'm_username' => $extends['m_username'],
			'wl_latitude' => $params['latitude'],
			'wl_longitude' => $params['longitude'],
			'wl_precision' => isset($params['precision']) ? $params['precision'] : 0,
			'wl_ip' => get_client_ip(),
			'wl_created' => NOW_TIME,
			'wl_updated' => NOW_TIME
		);
		return $this->_d->insert($weixin_data);
	}

    /**
     * 根据经纬度查询
     * @param $params
     * @return
     */
    public function get_by_conds_for_filter($params) {

        return $this->_d->get_by_conds_for_filter($params);
    }


}
