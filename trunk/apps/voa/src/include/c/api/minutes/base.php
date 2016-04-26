<?php
/**
 * voa_c_api_minutes_base
 * 会议记录基础控制器
 * $Author$
 * $Id$
 */
class voa_c_api_minutes_base extends voa_c_api_base {

	/** 插件id */
	protected $_pluginid = 0;


	public function __construct() {
		parent::__construct();
	}

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.minutes.setting', 'oa');

		/** 取应用插件信息 */
		$pluginid = $this->_p_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		if (array_key_exists($pluginid, $plugins)) {
			$this->_plugin = $plugins[$pluginid];
			startup_env::set('agentid', $this->_plugin['cp_agentid']);
			/** 加载提示语言 */
			language::load_lang($this->_plugin['cp_identifier']);
		}

		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 获取会议纪要配置
	 */
	public static function fetch_cache_minutes_setting() {
		$serv = &service::factory('voa_s_oa_minutes_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->fetch_all();
		$arr = array();
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['mis_type']) {
				$arr[$v['mis_key']] = unserialize($v['mis_value']);
			} else {
				$arr[$v['mis_key']] = $v['mis_value'];
			}
		}

		return $arr;
	}

	/**
	 * 获取查看详情的url
	 * @param string $url url地址
	 * @param int $mi_id 会议记录信息id
	 * @return boolean
	 */
	public function get_view_url(&$url, $mi_id) {
		/** 组织查看链接 */
		$scheme = config::get('voa.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($scheme.$this->_setting['domain'].'/minutes/view/'.$mi_id.'?pluginid='.startup_env::get('pluginid'));

		return true;
	}

	/**
	 * 更新草稿
	 * @param array $join_uids 接收人uids
	 * @param array $cc_uids 抄送人uids
	 */
	protected function _update_draft($join_uids = array(), $cc_uids = array()) {
		$serv = &service::factory('voa_s_oa_minutes_draft', array('pluginid' => startup_env::get('pluginid')));
		$mid_id = (int)$this->request->get('mid_id');
		if (0 < $mid_id) {
			$serv->update(array(
				'mid_message' => '',
				'mid_a_uid' => implode(',', $join_uids),
				'mid_cc_uid' => implode(',', array_diff($cc_uids, array($this->_user['m_uid'])))
			), array('mid_id' => $mid_id, 'm_openid' => $this->_user['m_openid']));
		} else {
			$serv->insert(array(
				'm_openid' => $this->_user['m_openid'],
				'mid_a_uid' => implode(',', $join_uids),
				'mid_cc_uid' => implode(',', array_diff($cc_uids, array($this->_user['m_uid'])))
			));
		}

		return true;
	}

	/**
	 * 获取草稿信息
	 * @param array &$ret 草稿内容
	 */
	protected function _get_draft(&$ret) {
		$serv_dr = &service::factory('voa_s_oa_minutes_draft', array('pluginid' => startup_env::get('pluginid')));
		$this->_draft = $serv_dr->get_by_openid($this->_user['m_openid']);
		if (empty($this->_draft)) {
			return true;
		}

		$this->view->set('mid_id', $this->_draft['mid_id']);
		/** 整理段落序号 */
		$msg = '';
		if (!empty($this->_draft['mid_message'])) {
			$arr = explode(config::get(startup_env::get('app_name').'.page_break'), $this->_draft['mid_message']);
			foreach ($arr as $k => $v) {
				$msg .= ($k + 1).' '.$v."\n";
			}
		}

		/** 取最近一次操作相关人员 */
		$uids = array();
		if (!empty($this->_draft['mid_cc_uid'])) {
			$uids = explode(',', $this->_draft['mid_cc_uid']);
		}

		$a_uids = array();
		if (!empty($this->_draft['mid_a_uid'])) {
			$a_uids = explode(',', $this->_draft['mid_a_uid']);
			$uids = array_merge($uids, $a_uids);
		}

		/** 取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
		$users = $serv_m->fetch_all_by_ids($uids);

		/** 输出接收人 */
		$ret = array();
		$accepters = array();
		if (!empty($this->_draft['mid_a_uid'])) {
			foreach ($a_uids as $uid) {
				if (!empty($users[$uid])) {
					$accepters[$uid] = $users[$uid];
					unset($users[$uid]);
				}
			}
		}

		$ret['ccusers'] = $users;
		$ret['accepters'] = $accepters;
		$ret['message'] = $msg;
		return true;
	}

}
