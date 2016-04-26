<?php
/**
 * 请假基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_askoff_base extends voa_c_frontend_base {

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		$this->view->set('navtitle', '请假');

		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.askoff.setting', 'oa');

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
	 * 获取请假配置
	 */
	public static function fetch_cache_askoff_setting() {

		$serv = &service::factory('voa_s_oa_askoff_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->fetch_all();
		$arr = array();
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['aos_type']) {
				$arr[$v['aos_key']] = unserialize($v['aos_value']);
			} else {
				$arr[$v['aos_key']] = $v['aos_value'];
			}
		}

		self::_check_agentid($arr, 'askoff');
		return $arr;
	}

	/** 显示操作菜单 */
	public static function show_menu($data, $plugin) {
		$serv = voa_wxqy_service::instance();
		/** 取草稿内容 */
		$content = $data['content'];
		$serv_dr = &service::factory('voa_s_oa_askoff_draft', array('pluginid' => startup_env::get('pluginid')));
		$draft = $serv_dr->get_by_openid($data['from_user_name']);
		if (!empty($draft['aod_message'])) {
			$content = $draft['aod_message'].config::get(startup_env::get('app_name').'.page_break').$content;
		}

		/** 更新草稿内容 */
		if (empty($draft)) {
			$serv_dr->insert(array(
				'm_openid' => $data['from_user_name'],
				'aod_message' => $content
			));
		} else {
			$serv_dr->update(array('aod_message' => $content), array('m_openid' => $data['from_user_name']));
		}

		/** 整理段落序号 */
		$arr = explode(config::get(startup_env::get('app_name').'.page_break'), $content);
		$msg = '';
		foreach ($arr as $k => $v) {
			$msg .= ($k + 1).' '.$v."\n";
		}

		$viewurl = $serv->oauth_url_base(voa_h_func::get_agent_url('/askoff/new', $plugin['cp_pluginid']));
		$ret = "请假内容：\n"
			 . $msg."\n"
			 . "===操作===\n"
			 . ' <a href="'.$viewurl.'">生成请假</a> 快速提交'."\n"
			 . "没写完可以接着写哦";

		return $ret;
	}

	/**
	 * 获取查看详情的url
	 * @param string $url url地址
	 * @param int $ao_id 请假信息id
	 * @return boolean
	 */
	public function get_view_url(&$url, $ao_id) {
		/** 组织查看链接 */
		$scheme = config::get('voa.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($scheme.$this->_setting['domain'].'/askoff/view/'.$ao_id.'?pluginid='.startup_env::get('pluginid'));

		return true;
	}

	/**
	 * 更新草稿
	 * @param array $a_uid 接收人uid
	 * @param array $cc_uids 抄送人uids
	 */
	protected function _update_draft($a_uid = 0, $cc_uids = array()) {
		$serv = &service::factory('voa_s_oa_askoff_draft', array('pluginid' => startup_env::get('pluginid')));
		$aod_id = (int)$this->request->get('aod_id');
		if (0 < $aod_id) {
			$serv->update(array(
				'aod_message' => '',
				'aod_a_uid' => $a_uid,
				'aod_cc_uid' => implode(',', array_diff($cc_uids, array($this->_user['m_uid'])))
			), array('aod_id' => $aod_id, 'm_openid' => $this->_user['m_openid']));
		} else {
			$serv->insert(array(
				'm_openid' => $this->_user['m_openid'],
				'aod_a_uid' => $a_uid,
				'aod_cc_uid' => implode(',', array_diff($cc_uids, array($this->_user['m_uid'])))
			));
		}

		return true;
	}

	/**
	 * 获取草稿信息
	 * @param array &$ret 草稿内容
	 */
	protected function _get_draft(&$ret) {
		$serv_dr = &service::factory('voa_s_oa_askoff_draft', array('pluginid' => startup_env::get('pluginid')));
		$this->_draft = $serv_dr->get_by_openid($this->_user['m_openid']);
		if (empty($this->_draft)) {
			return true;
		}

		$this->view->set('aod_id', $this->_draft['aod_id']);
		/** 整理段落序号 */
		$msg = '';
		if (!empty($this->_draft['aod_message'])) {
			$arr = explode(config::get(startup_env::get('app_name').'.page_break'), $this->_draft['aod_message']);
			foreach ($arr as $k => $v) {
				$msg .= ($k + 1).' '.$v."\n";
			}
		}

		/** 取最近一次操作相关人员 */
		$uids = array();
		if (!empty($this->_draft['aod_cc_uid'])) {
			$uids = explode(',', $this->_draft['aod_cc_uid']);
		}

		if (!empty($this->_draft['aod_a_uid'])) {
			$uids[] = $this->_draft['aod_a_uid'];
		}

		/** 取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
		$users = $serv_m->fetch_all_by_ids($uids);

		/** 输出接收人 */
		$ret = array();
		if (!empty($this->_draft['aod_a_uid'])) {
			$ret['accepter'] = $users[$this->_draft['aod_a_uid']];
			unset($users[$this->_draft['aod_a_uid']]);
		}

		$ret['ccusers'] = $users;
		$ret['message'] = $msg;
		return true;
	}
}
