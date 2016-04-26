<?php
/**
 * 商品基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_goods_base extends voa_c_frontend_base {

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_pluginname = 'goods';

		$this->view->set('navtitle', '商品');
		$this->view->set('goods_set', $this->_p_sets);

		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {
		/** 取应用配置 */
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.goods.setting', 'oa');

		/** 取应用插件信息 */
		$pluginid = $this->_p_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		// 如果应用信息不存在
		if (!array_key_exists($pluginid, $plugins)) {
			$this->_error_message('应用信息丢失，请重新开启');
			return true;
		}

		// 获取应用信息
		$this->_plugin = $plugins[$pluginid];

		// 判断应用是否关闭
		if ($this->_plugin['cp_available'] != voa_d_oa_common_plugin::AVAILABLE_OPEN) {
			$this->_error_message('本应用尚未开启 或 已关闭，请联系管理员启用后使用');
			return true;
		}

		startup_env::set('agentid', $this->_plugin['cp_agentid']);
		/** 加载提示语言 */
		language::load_lang($this->_plugin['cp_identifier']);

		return true;
	}

	/**
	 * 获取商品配置
	 */
	public static function fetch_cache_goods_setting() {

		// 取当前应用配置
		$t = new voa_d_oa_goods_setting();
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
	 * 获取商品分类
	 */
	public static function fetch_cache_goods_class() {

		// 取配置缓存
		$sets = voa_h_cache::get_instance()->get('plugin.goods.setting', 'oa');
		// 取表格缓存
		$tables = voa_h_cache::get_instance()->get('goodstable', 'oa');
		// 取分类信息
		$t = new voa_d_oa_goods_class();
		$list = $t->list_by_tid($tables[$sets['goods_table_name']]['tid']);

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
	 * 获取表格列信息
	 * @return multitype:unknown
	 */
	public static function fetch_cache_goods_tablecol() {

		// 取配置缓存
		$sets = voa_h_cache::get_instance()->get('plugin.goods.setting', 'oa');
		// 取表格缓存
		$tables = voa_h_cache::get_instance()->get('goodstable', 'oa');
		// 读取数据列表
		$t = new voa_d_oa_goods_tablecol();
		$list = $t->list_by_tid($tables[$sets['goods_table_name']]['tid'], array(), array('orderid' => 'desc'));

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

			$ret[$_v['tc_id']] = $_v;
		}

		return $ret;
	}

	/**
	 * 获取表格列选项信息
	 * @return multitype:unknown
	 */
	public static function fetch_cache_goods_tablecolopt() {

		// 取配置缓存
		$sets = voa_h_cache::get_instance()->get('plugin.goods.setting', 'oa');
		// 取表格缓存
		$tables = voa_h_cache::get_instance()->get('goodstable', 'oa');
		// 读取数据列表
		$t = new voa_d_oa_goods_tablecolopt();
		if (!$list = $t->list_by_tid($tables[$sets['goods_table_name']]['tid'])) {
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


	/**
	 * 获取报告配置
	 */
	public static function fetch_cache_goods_goodsexpress() {
		// 读取数据列表
		$t = new voa_d_oa_goods_express();
		$ret = array();
		if (!$list = $t->list_all()) {
			return array();
		}
		foreach ($list as $_v) {
			$ret[] = $_v;
		}

		//$ret['pluginid'] = startup_env::get('pluginid');
		return $ret;
	}

}
