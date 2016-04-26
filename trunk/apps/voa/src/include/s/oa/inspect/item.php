<?php
/**
 * 巡店打分项信息表
 * $Author$
 * $Id$
 */

class voa_s_oa_inspect_item extends voa_s_abstract {

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
	 * @param array &$item 评分信息
	 * @return boolean
	 */
	public function format(&$item) {

		// 发起时间
		$item['_created'] = rgmdate($item['insi_created'], 'Y-m-d H:i');
		$item['_updated'] = rgmdate($item['insi_updated'], 'Y-m-d H:i');
		list($item['_created_ymd'], $item['_created_hi']) = explode(' ', $item['_created']);
		// 个性化发起时间
		$item['_updated_u'] = rgmdate($item['insi_updated'], 'u');

		return true;
	}

}

