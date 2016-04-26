<?php
/**
 * 客户基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_customer_base extends voa_c_frontend_base {

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		/** 取应用配置 */
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.customer.setting', 'oa');

		/** 取应用插件信息 */
		$pluginid = $this->_p_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		// 如果当前应用存在
		if (array_key_exists($pluginid, $plugins)) {
			$this->_plugin = $plugins[$pluginid];
			startup_env::set('agentid', $this->_plugin['cp_agentid']);
			/** 加载提示语言 */
			language::load_lang($this->_plugin['cp_identifier']);
		}

		$this->view->set('navtitle', '客户');
		$this->view->set('customer_set', $this->_p_sets);

		return true;
	}

	/**
	 * 获取客户配置
	 */
	public static function fetch_cache_customer_setting() {

		// 取当前应用配置
		$t = new voa_d_oa_customer_setting();
		$data = $t->list_all();

		// 解析数据
		$arr = array();
		foreach ($data as $v) {
			// 如果当前数据类型为数组
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['type']) {
				$arr[$v['skey']] = unserialize($v['value']);
			} else {
				$arr[$v['skey']] = $v['value'];
			}
		}

		return $arr;
	}

	/**
	 * 获取客户分类
	 */
	public static function fetch_cache_customer_class() {

		// 取分类信息
		$t = new voa_d_oa_customer_class();
		$list = $t->list_all();

		// 格式化分类数据, 建立一个 pid => classid 的关系数组
		$data = array(
			'p2c' => array()
		);
		foreach ($list as $_v) {
			// 如果没有以当前 pid 为键值的数据
			if (!array_key_exists($_v['pid'], $data['p2c'])) {
				$data['p2c'][$_v['pid']] = array();
			}

			$data[$_v['classid']] = $_v;
			$data['p2c'][$_v['pid']][$_v['classid']] = $_v['classid'];
		}

		return $data;
	}

	/**
	 * 获取表格列表
	 * @return multitype:unknown
	 */
	public static function fetch_cache_customer_table() {

		// 读取数据列表
		$t = new voa_d_oa_customer_table();
		$list = $t->list_all();

		// 格式化表格数据, 建立一个 tid => tunique 的关系数组
		$ret = array(
			'tid2tunique' => array()
		);
		// 遍历
		foreach ($list as $_v) {
			$ret['tid2tunique'][$_v['tid']] = $_v['tunique'];
			$ret[$_v['tunique']] = $_v;
		}

		return $ret;
	}

	/**
	 * 获取表格列信息
	 * @return multitype:unknown
	 */
	public static function fetch_cache_customer_tablecol() {

		// 读取数据列表
		$t = new voa_d_oa_customer_tablecol();
		$list = $t->list_all(null, array('orderid' => 'desc'));

		// 重新组合数据, 按 tid => array(tc_id => array(...))
		$ret = array();
		foreach ($list as $_v) {
			// 如果没有以当前 tid 为键值的数据
			if (!array_key_exists($_v['tid'], $ret)) {
				$ret[$_v['tid']] = array();
			}

			// 如果 field 为空
			if (empty($_v['field'])) {
				$_v['field'] = '_'.$_v['tc_id'];
			}

			$ret[$_v['tid']][$_v['tc_id']] = $_v;
		}

		return $ret;
	}

	/**
	 * 获取表格列选项信息
	 * @return multitype:unknown
	 */
	public static function fetch_cache_customer_tablecolopt() {

		// 读取数据列表
		$t = new voa_d_oa_customer_tablecolopt();
		if (!$list = $t->list_all()) {
			return array();
		}

		// 重新组合数据, 按 tc_id => array(tco_id => array(...))
		$ret = array();
		foreach ($list as $_v) {
			// 如果没有以当前 tc_id 为键值的数据
			if (!array_key_exists($_v['tc_id'], $ret)) {
				$ret[$_v['tc_id']] = array();
			}

			$ret[$_v['tc_id']][$_v['tco_id']] = $_v;
		}

		return $ret;
	}

}
