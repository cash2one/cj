<?php
/**
 * 审批基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_askfor_base extends voa_c_frontend_base {

	/** 更新时间 */
	protected $_updated;

	protected $_askfor_status_descriptions = array(
		voa_d_oa_askfor::STATUS_NORMAL => '审批中',
		voa_d_oa_askfor::STATUS_APPROVE => '已批准',
		voa_d_oa_askfor::STATUS_APPROVE_APPLY => '通过并转审批',
		voa_d_oa_askfor::STATUS_REFUSE => '审核未通过',
		voa_d_oa_askfor::STATUS_DRAFT => '草稿',
		voa_d_oa_askfor::STATUS_REMINDER => '已催办',
		voa_d_oa_askfor::STATUS_CANCEL => '已撤销',
		//voa_d_oa_askfor::STATUS_REMOVE => '已删除',
	);

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_mobile_tpl = true;
		$this->view->set('navtitle', '审批');

		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.askfor.setting', 'oa');

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
	public static function fetch_cache_askfor_setting() {
		$serv = &service::factory('voa_s_oa_askfor_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->fetch_all();
		$arr = array();
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['afs_type']) {
				$arr[$v['afs_key']] = unserialize($v['aos_value']);
			} else {
				$arr[$v['afs_key']] = $v['afs_value'];
			}
		}

		self::_check_agentid($arr, 'askfor');
		return $arr;
	}

	/**
	 * 获取查看详情的url
	 * @param string $url url地址
	 * @param int $af_id 审批信息id
	 * @return boolean
	 */
	public function get_view_url(&$url, $af_id) {
		/** 组织查看链接 */
		$scheme = config::get('voa.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($scheme.$this->_setting['domain'].'/askfor/view/'.$af_id.'?pluginid='.startup_env::get('pluginid'));

		return true;
	}

	/**
	 * 格式化审批列表
	 * @param array $list 审批列表
	 */
	protected  function _format_askfor_list($list) {
		if (!empty($list)){
			foreach ($list as &$v) {
				$v['af_subject'] = rhtmlspecialchars($v['af_subject']);
				$v['_created'] = rgmdate($v['af_created'], 'u');
				$v['_status'] = isset($this->_askfor_status_descriptions[$v['af_status']]) ? $this->_askfor_status_descriptions[$v['af_status']] : '';
				$this->_updated = $v['af_updated'];
			}
		}
	}
}
