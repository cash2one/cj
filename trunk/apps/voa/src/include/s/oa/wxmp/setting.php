<?php
/**
 * 公众号配置
 * $Author$
 * $Id$
 */

class voa_s_oa_wxmp_setting extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 格式化评分项数据
	 * @param array &$setting 评分信息
	 * @return boolean
	 */
	public function format(&$setting) {

		// 发起时间
		$setting['_created'] = rgmdate($setting['created'], 'Y-m-d H:i');
		$setting['_updated'] = rgmdate($setting['updated'], 'Y-m-d H:i');
		list($setting['_created_ymd'], $setting['_created_hi']) = explode(' ', $setting['_created']);
		// 个性化发起时间
		$setting['_updated_u'] = rgmdate($setting['updated'], 'u');

		return true;
	}

}

