<?php
/**
 * voa_uda_frontend_travel_setting
 * 统一数据访问/商品应用/配置操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_setting extends voa_uda_frontend_travel_abstract {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 获取列表
	 * @param array &$ret 读取列表
	 * @return boolean
	 */
	public function list_all(&$ret) {

		// 读取配置数据
		$t = new voa_d_oa_travel_setting();
		$data = $t->list_all();

		$ret = array();
		foreach ($data as $_v) {
			// 如果该值为数组
			if (voa_d_oa_common_setting::TYPE_ARRAY == $_v['type']) {
				$ret[$_v['skey']] = unserialize($_v['value']);
			} else {
				$ret[$_v['skey']] = $_v['value'];
			}
		}

		return true;
	}

	/**
	 * 更新配置信息
	 * @param array &$data 读取列表
	 * @return boolean
	 */
	public function update($data) {

		$t = new voa_d_oa_travel_setting();
		foreach ($data as $_k => $_v) {
			$t->update($_k, array('value' => $_v));
		}

		return true;
	}

}
