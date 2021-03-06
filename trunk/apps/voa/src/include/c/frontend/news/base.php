<?php
/**
 * 新闻公告基类
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_news_base extends voa_c_frontend_base {

	protected $_askfor_status_descriptions = array(
		voa_d_oa_askfor::STATUS_NORMAL => '审批中',
		voa_d_oa_askfor::STATUS_APPROVE => '已批准',
		voa_d_oa_askfor::STATUS_APPROVE_APPLY => '通过并转审批',
		voa_d_oa_askfor::STATUS_REFUSE => '审核未通过',
		voa_d_oa_askfor::STATUS_DRAFT => '草稿',
		//voa_d_oa_askfor::STATUS_REMOVE => '已删除',
	);

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}
		$this->_mobile_tpl = true;
		$this->view->set('navtitle', '新闻公告');

		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.news.setting', 'oa', true);

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
	 * 获取审批配置
	 */
	public static function fetch_cache_news_setting() {

		$serv = &service::factory('voa_s_oa_news_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->list_all();
		$arr = array();
		if (! empty($data)) {
			foreach ($data as $v) {
				if (voa_d_oa_common_setting::TYPE_ARRAY == $v['type']) {
					$arr[$v['key']] = unserialize($v['value']);
				} else {
					$arr[$v['key']] = $v['value'];
				}
			}
		}

		self::_check_agentid($arr, 'news');
		return $arr;
	}

	/**
	 * 获取查看详情的url
	 * @param string $url url地址
	 * @param int $af_id 审批信息id
	 * @return boolean
	 */
	public function get_view_url(&$url, $af_id, $auth = false) {
		/** 组织查看链接 */
		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme.$this->_setting['domain'].'/frontend/news/view/?ne_id='.$af_id.'&pluginid='.startup_env::get('pluginid');
		if (!$auth) {
			return $url;
		}
		$url = voa_wxqy_service::instance()->oauth_url($url);

		return true;
	}
}
