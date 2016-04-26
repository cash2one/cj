<?php
/**
 * 备忘基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_vnote_base extends voa_c_frontend_base {

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		$this->view->set('navtitle', '备忘录');

		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.vnote.setting', 'oa');

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
	 * 获取备忘配置
	 */
	public static function fetch_cache_vnote_setting() {

		$serv = &service::factory('voa_s_oa_vnote_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->fetch_all();
		$arr = array();
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['vns_type']) {
				$arr[$v['vns_key']] = unserialize($v['vns_value']);
			} else {
				$arr[$v['vns_key']] = $v['vns_value'];
			}
		}

		self::_check_agentid($arr, 'vnote');
		return $arr;
	}

	/** 显示操作菜单 */
	public static function show_menu($data, $plugin) {
		$serv = voa_wxqy_service::instance();
		/** 取草稿内容 */
		$content = $data['content'];
		$serv_dr = &service::factory('voa_s_oa_vnote_draft', array('pluginid' => startup_env::get('pluginid')));
		$draft = $serv_dr->get_by_openid($data['from_user_name']);
		if (!empty($draft['vnd_message'])) {
			$content = $draft['vnd_message'].config::get(startup_env::get('app_name').'.page_break').$content;
		}

		/** 更新草稿内容 */
		if (empty($draft)) {
			$serv_dr->insert(array(
				'm_openid' => $data['from_user_name'],
				'vnd_message' => $content
			));
		} else {
			$serv_dr->update(array('vnd_message' => $content), array('m_openid' => $data['from_user_name']));
		}

		/** 整理段落序号 */
		$arr = explode(config::get(startup_env::get('app_name').'.page_break'), $content);
		$msg = '';
		foreach ($arr as $k => $v) {
			$msg .= ($k + 1).' '.$v."\n";
		}

		$viewurl = $serv->oauth_url_base(voa_h_func::get_agent_url('/vnote/new', $plugin['cp_pluginid']));
		$ret = "备忘录：\n"
			 . $msg."\n"
			 . "===操作===\n"
			 . ' <a href="'.$viewurl.'">生成备忘</a> 快速提交'."\n"
			 . "没写完可以接着写哦";

		return $ret;
	}

	/**
	 * 获取查看详情的url
	 * @param string $url url地址
	 * @param int $vn_id 微信墙信息id
	 * @return boolean
	 */
	public function get_view_url(&$url, $vn_id) {
		/** 组织查看链接 */
		$scheme = config::get('voa.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($scheme.$this->_setting['domain'].'/vnote/view/'.$vn_id.'?pluginid='.startup_env::get('pluginid'));

		return true;
	}

	/**
	 * 更新草稿
	 * @param array $cc_uids 抄送人uids
	 */
	protected function _update_draft($cc_uids = array()) {
		$serv = &service::factory('voa_s_oa_vnote_draft', array('pluginid' => startup_env::get('pluginid')));
		$vnd_id = (int)$this->request->get('vnd_id');
		if (0 < $vnd_id) {
			$serv->update(array(
				'vnd_message' => '',
				'vnd_cc_uid' => implode(',', array_diff($cc_uids, array($this->_user['m_uid'])))
			), array('vnd_id' => $vnd_id, 'm_openid' => $this->_user['m_openid']));
		} else {
			$serv->insert(array(
				'm_openid' => $this->_user['m_openid'],
				'vnd_cc_uid' => implode(',', array_diff($cc_uids, array($this->_user['m_uid'])))
			));
		}

		return true;
	}

	/**
	 * 获取草稿信息
	 * @param array &$ret 草稿内容
	 */
	protected function _get_draft(&$ret) {
		$serv_dr = &service::factory('voa_s_oa_vnote_draft', array('pluginid' => startup_env::get('pluginid')));
		$this->_draft = $serv_dr->get_by_openid($this->_user['m_openid']);
		if (empty($this->_draft)) {
			return true;
		}

		$this->view->set('vnd_id', $this->_draft['vnd_id']);
		/** 整理段落序号 */
		$msg = '';
		if (!empty($this->_draft['vnd_message'])) {
			$arr = explode(config::get(startup_env::get('app_name').'.page_break'), $this->_draft['vnd_message']);
			foreach ($arr as $k => $v) {
				$msg .= ($k + 1).' '.$v."\n";
			}
		}

		/** 取最近一次操作相关人员 */
		$uids = array();
		if (!empty($this->_draft['vnd_cc_uid'])) {
			$uids = explode(',', $this->_draft['vnd_cc_uid']);
		}

		/** 取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
		$users = $serv_m->fetch_all_by_ids($uids);
		unset($users[startup_env::get('wbs_uid')]);

		$ret = array('ccusers' => $users, 'message' => $msg);
		return true;
	}

	/**
	 * 把消息推入队列
	 * @param array $vnote 备忘录信息
	 * @param array $cculist 需要发送的用户
	 * @return boolean
	 */
	protected function _to_queue($vnote, $cculist) {
		/** 整理需要接收消息的用户 */
		$users = array();
		foreach ($cculist as $u) {
			if (startup_env::get('wbs_uid') == $u['m_uid']) {
				continue;
			}

			$users[$u['m_uid']] = $u['m_openid'];
		}

		/** 如果没有需要发送的用户 */
		if (empty($users)) {
			return true;
		}

		/** 整理输出 */
		$uda_fmt = &uda::factory('voa_uda_frontend_vnote_format');
		if (!$uda_fmt->format($vnote)) {
			$this->_error_message($uda_fmt->error, get_referer());
			return false;
		}

		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $vnote['vn_id']);
		$content = "来自 ".$vnote['m_username']." 的备忘分享\n"
				 . "<a href='".$viewurl."'>点击查看详情</a>";

		$data = array(
			'mq_touser' => implode('|', $users),
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => (int)$this->_plugin['cp_agentid'],
			'mq_message' => $content
		);
		voa_h_qymsg::push_send_queue($data);

		/** 写入 cookie, 刷新页面时发送 */
		$this->set_queue_session(array($data['mq_id']));
	}
}
