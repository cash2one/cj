<?php
/**
 * Class voa_c_frontend_xdf_base
 * 新东方登录基本控制
 * @create-time: 2015-06-17
 * @author: huanw
 * @email: wanghuan@vchangyi.com
 */

class voa_c_frontend_xdf_base extends voa_c_frontend_base {

	//生成二维码url基本路径
	public $qrcodelogin_url_base = '';

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->qrcodelogin_url_base = config::get("voa.oa_http_scheme") . $_SERVER['HTTP_HOST'] . '/frontend/xdf/qrcodelogin';
		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.xdf.setting', 'oa');

		/** 取应用插件信息 */
		$pluginid = !empty($this->_p_sets['pluginid']) ? $this->_p_sets['pluginid'] : 0;
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
	 * 获取审批配置
	 */
	public static function fetch_cache_xdf_setting() {

		$serv = &service::factory('voa_s_oa_xdf_setting');
		$data = $serv->list_all();
		$arr = array();
		$_pluginid = - 1;
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['type']) {
				$arr[$v['key']] = unserialize($v['value']);
			} else {
				$arr[$v['key']] = $v['value'];
			}

			if ($v['key'] == 'pluginid') {
				$_pluginid = $v['value'];
			}
		}

		self::_check_agentid($arr, 'xdf');
		return $arr;
	}

}
