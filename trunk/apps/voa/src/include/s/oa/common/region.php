<?php
/**
 * 地区信息表
 * $Author$
 * $Id$
 */

class voa_s_oa_common_region extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 格式化地区数据
	 * @param array &$region 地区信息
	 * @return boolean
	 */
	public function format(&$region) {

		// 发起时间
		$region['_created'] = rgmdate($region['cr_created'], 'Y-m-d H:i');
		list($region['_created_ymd'], $region['_created_hi']) = explode(' ', $region['_created']);
		// 个性化发起时间
		$region['_updated_u'] = rgmdate($region['cr_updated'], 'u');

		return true;
	}

}
