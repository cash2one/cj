<?php
/**
 * 销售轨迹基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_footprint_base extends voa_c_frontend_base {
	/** 完成状态类型 */
	public $type_done = 4;

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.footprint.setting', 'oa');

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
	 * 获取销售轨迹配置
	 */
	public static function fetch_cache_footprint_setting() {

		$serv = &service::factory('voa_s_oa_footprint_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->fetch_all();
		$arr = array();
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['fps_type']) {
				$arr[$v['fps_key']] = unserialize($v['fps_value']);
			} else {
				$arr[$v['fps_key']] = $v['fps_value'];
			}
		}

		self::_check_agentid($arr, 'footprint');
		return $arr;
	}

	/**
	 * 根据轨迹id获取附件信息
	 * @param array $fp_ids
	 * @param array $attachs
	 */
	protected function _fetch_attach_by_fp_id($fp_ids, &$attachs) {
		/** 读取附件信息 */
		$serv = &service::factory('voa_s_oa_footprint_attachment', array('pluginid' => startup_env::get('pluginid')));
		$fp_attachs = $serv->fetch_by_fp_id($fp_ids);

		/** 按 fp_id 整理附件数据 */
		$fmt = &uda::factory('voa_uda_frontend_attachment_format');
		foreach ($fp_attachs as $att) {
			$fmt->format($att);
			if (!array_key_exists($att['fp_id'], $attachs)) {
				$attachs[$att['fp_id']] = array();
			}

			$attachs[$att['fp_id']][$att['at_id']] = $att;
		}

		return true;
	}

	/**
	 * 根据轨迹id获取回复信息
	 * @param array $fp_ids
	 * @param array $posts
	 * @return boolean
	 */
	protected function _fetch_post_by_fp_id($fp_ids, &$posts) {
		/** 读取回复信息 */
		$serv = &service::factory('voa_s_oa_footprint_post', array('pluginid' => startup_env::get('pluginid')));
		$fp_posts = $serv->fetch_by_fp_id($fp_ids);

		$fmt = &uda::factory('voa_uda_frontend_footprint_format');
		foreach ($fp_posts as $p) {
			$fmt->format_post($p);
			if (!array_key_exists($p['fp_id'], $posts)) {
				$posts[$p['fp_id']] = array();
			}

			$posts[$p['fp_id']][$p['fppt_id']] = $p;
		}

		return true;
	}
}
