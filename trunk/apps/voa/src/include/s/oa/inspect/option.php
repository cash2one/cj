<?php
/**
 * 巡店选项信息表
 * $Author$
 * $Id$
 */

class voa_s_oa_inspect_option extends voa_s_abstract {

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
	 * @param array &$option 评分信息
	 * @return boolean
	 */
	public function format(&$option) {

		// 发起时间
		$option['_created'] = rgmdate($option['inso_created'], 'Y-m-d H:i');
		$option['_updated'] = rgmdate($option['inso_updated'], 'Y-m-d H:i');
		list($option['_created_ymd'], $option['_created_hi']) = explode(' ', $option['_created']);
		// 个性化发起时间
		$option['_updated_u'] = rgmdate($option['inso_updated'], 'u');

		return true;
	}

}

