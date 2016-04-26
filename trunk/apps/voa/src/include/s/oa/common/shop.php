<?php
/**
 * 门店信息表
 * $Author$
 * $Id$
 */

class voa_s_oa_common_shop extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 格式化门店数据
	 * @param array &$task 门店信息
	 * @return boolean
	 */
	public function format(&$shop) {

		// 发起时间
		$shop['_created'] = rgmdate($shop['csp_created'], 'Y-m-d H:i');
		list($shop['_created_ymd'], $shop['_created_hi']) = explode(' ', $shop['_created']);
		// 个性化发起时间
		$shop['_updated_u'] = rgmdate($shop['csp_updated'], 'u');

		return true;
	}

}
