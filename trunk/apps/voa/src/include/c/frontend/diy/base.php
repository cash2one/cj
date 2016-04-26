<?php
/**
 * diy 基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_diy_base extends voa_c_frontend_base {

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->view->set('navtitle', 'DIY');

		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

		return true;
		// 取应用配置
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.diy.setting', 'oa');

		// 取应用插件信息
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
		// 加载提示语言
		language::load_lang($this->_plugin['cp_identifier']);

		return true;
	}

	// 显示操作菜单
	public static function show_menu($data, $plugin) {


		return '查询功能暂未开放';
	}

	// 获取商品配置
	public static function fetch_cache_diy_setting() {

		return true;
		// 取当前应用配置
		/**$t = new voa_d_oa_diy_setting();
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

		return $arr;*/
	}

	/**
	 * 获取表格列信息
	 * @return multitype:unknown
	 */
	public static function fetch_cache_diy_tablecol() {

		// 取表格缓存
		$tables = voa_h_cache::get_instance()->get('diytable', 'oa');
		$tname = 'test';

		// 读取数据列表
		$t = new voa_d_oa_diy_tablecol();
		$list = $t->list_by_tid($tables[$tname]['tid'], array(), array('orderid' => 'desc'));

		// 重新组合数据, 按 tid => array(tc_id => array(...))
		$ret = array();
		foreach ($list as $_v) {
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
	public static function fetch_cache_diy_tablecolopt() {

		// 取表格缓存
		$tables = voa_h_cache::get_instance()->get('diytable', 'oa');
		$tname = 'test';

		// 读取数据列表
		$t = new voa_d_oa_diy_tablecolopt();
		if (!$list = $t->list_by_tid($tables[$tname]['tid'])) {
			return array();
		}

		// 重新组合数据, 按 tc_id => array(tco_id => array(...))
		$ret = array();
		$p2c = array();
		foreach ($list as $_v) {
			// 如果没有以当前 tc_id 为键值的数据
			if (!array_key_exists($_v['tc_id'], $p2c)) {
				$p2c[$_v['tc_id']] = array();
			}

			$_v['attachurl'] = '';
			if (!empty($_v['attachid'])) {
				$_v['attachurl'] = voa_h_attach::attachment_url($_v['attachid']);
			}

			$p2c[$_v['tc_id']][$_v['tco_id']] = $_v['tco_id'];
			$ret[$_v['tco_id']] = $_v;
		}

		$ret['p2c'] = $p2c;

		return $ret;
	}

}
