<?php
/**
 * 微信墙基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_wxwall_base extends voa_c_frontend_base {
	/** 申请中 */
	const STATUS_NORMAL = 1;
	/** 已通过(已批准) */
	const STATUS_APPROVE = 2;
	/** 审批不通过 */
	const STATUS_REFUSE = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;

	/** 关闭 */
	const IS_CLOSE = 0;
	/** 开启 */
	const IS_OPEN = 1;

	public function _before_action() {

		return parent::_before_action();
	}

	// 获取插件信息
	protected function _get_plugin() {

		/** 获取投票配置 */
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.wxwall.setting', 'oa');

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
	 * 获取微信墙配置
	 */
	public static function fetch_cache_wxwall_setting() {

		$serv = &service::factory('voa_s_oa_wxwall_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->fetch_all();
		$arr = array();
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['wws_type']) {
				$arr[$v['wws_key']] = unserialize($v['wws_value']);
			} else {
				$arr[$v['wws_key']] = $v['wws_value'];
			}
		}

		self::_check_agentid($arr, 'wxwall');
		return $arr;
	}

	/**
	 * 获取查看详情的url
	 * @param string $url url地址
	 * @param int $ww_id 微信墙信息id
	 * @return boolean
	 */
	public function get_view_url(&$url, $ww_id) {
		/** 组织查看链接 */
		$scheme = config::get('voa.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($scheme.$this->_setting['domain'].'/wxwall/view/'.$ww_id.'?pluginid='.startup_env::get('pluginid'));

		return true;
	}
}
