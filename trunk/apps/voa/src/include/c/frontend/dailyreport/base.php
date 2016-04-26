<?php
/**
 * 报告基类
 * $Author$
 * $Id$
 */

class voa_c_frontend_dailyreport_base extends voa_c_frontend_base {

	protected function _before_action($action) {

		// 使用手机H5新模板
		$this->_mobile_tpl = true;
		if (! parent::_before_action($action)) {
			return false;
		}

		$this->view->set('navtitle', '日报');
		$this->view->set('css_file', 'app_dailyreport.css');
		return true;
	}

	/**
	 * 获取插件信息
	 */
	protected function _get_plugin() {
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.dailyreport.setting', 'oa');

		// 取应用插件信息
		$pluginid = $this->_p_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		// 如果应用信息不存在
		if (! array_key_exists($pluginid, $plugins)) {
			voa_h_cache::get_instance()->get('plugin.dailyreport.setting', 'oa', true);
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

	/**
	 * 获取报告配置
	 */
	public static function fetch_cache_dailyreport_setting() {

		$serv = &service::factory('voa_s_oa_dailyreport_setting');
		$data = $serv->fetch_all();
		$arr = array();
		$_pluginid = - 1;
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['drs_type']) {
				$arr[$v['drs_key']] = unserialize($v['drs_value']);
			} else {
				$arr[$v['drs_key']] = $v['drs_value'];
			}
			if ($v['drs_key'] == 'pluginid') {
				$_pluginid = $v['drs_value'];
			}
		}

		self::_check_agentid($arr, 'dailyreport');
		return $arr;
	}

	/**
	 * 显示操作菜单
	 */
	public static function show_menu($data, $plugin) {
		$serv = voa_wxqy_service::instance();
		// 取草稿内容
		$content = $data['content'];
		$serv_dr = &service::factory('voa_s_oa_dailyreport_draft', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$draft = $serv_dr->get_by_openid($data['from_user_name']);
		if (! empty($draft['drd_message'])) {
			$content = $draft['drd_message'] . config::get(startup_env::get('app_name') . '.page_break') . $content;
		}

		// 更新草稿内容
		if (empty($draft)) {
			$serv_dr->insert(array(
				'm_openid' => $data['from_user_name'],
				'drd_message' => $content
			));
		} else {
			$serv_dr->update(array(
				'drd_message' => $content
			), array(
				'm_openid' => $data['from_user_name']
			));
		}

		// 整理段落序号
		$arr = explode(config::get(startup_env::get('app_name') . '.page_break'), $content);
		$msg = '';
		foreach ($arr as $k => $v) {
			$msg .= ($k + 1) . ' ' . $v . "\n";
		}

		//$viewurl = $serv->oauth_url_base(voa_h_func::get_agent_url('/dailyreport/new', $plugin['cp_pluginid']));
		$viewurl = voa_h_func::get_agent_url('/dailyreport/new', $plugin['cp_pluginid']);
		$ret = "日报内容：\n" . $msg . "\n" . "===操作===\n"
				. ' <a href="' . $viewurl . '">生成日报</a> 快速提交'
				. "\n" . "没写完可以接着写哦";

		return $ret;
	}

	/**
	 * 获取查看详情的url
	 * @param string $url url地址
	 * @param int $dr_id 日报信息id
	 * @return boolean
	 */
	public function get_view_url(&$url, $dr_id) {
		/** 组织查看链接 */
		$scheme = config::get('voa.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($scheme.$this->_setting['domain'].'/dailyreport/view/'.$dr_id.'?pluginid='.startup_env::get('pluginid'));

		return true;
	}

	/**
	 * 更新草稿
	 * @param array $a_uid 接收人uid
	 * @param array $cc_uids 抄送人uids
	 */
	protected function _update_draft($m_uid = array(), $cc_uids = array()) {
		$serv = &service::factory('voa_s_oa_dailyreport_draft', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$drd_id = (int) $this->request->get('drd_id');
		if (0 < $drd_id) {
			$serv->update(array(
				'drd_message' => '',
				'drd_a_uid' => implode(',', $m_uid),
				'drd_cc_uid' => implode(',', array_diff($cc_uids, array(
					$this->_user['m_uid']
				)))
			), array(
				'drd_id' => $drd_id,
				'm_openid' => $this->_user['m_openid']
			));
		} else {
			$serv->insert(array(
				'm_openid' => $this->_user['m_openid'],
				'drd_a_uid' => implode(',', $m_uid),
				'drd_cc_uid' => implode(',', array_diff($cc_uids, array(
					$this->_user['m_uid']
				)))
			));
		}

		return true;
	}

	/**
	 * 获取草稿信息
	 * @param array &$ret 草稿内容
	 */
	protected function _get_draft(&$ret) {
		$serv_dr = &service::factory('voa_s_oa_dailyreport_draft', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$this->_draft = $serv_dr->get_by_openid($this->_user['m_openid']);
		if (empty($this->_draft)) {

			// 如果草稿为空，则取字段默认值 by Deepseath@20141222#310
			$ret = $serv_dr->fetch_all_field();
			$ret['ccusers'] = array();
			$ret['accepter'] = array();
			$ret['message'] = '';

			return true;
		}

		$this->view->set('drd_id', $this->_draft['drd_id']);
		// 整理段落序号
		$msg = '';
		if (! empty($this->_draft['drd_message'])) {
			$arr = explode(config::get(startup_env::get('app_name') . '.page_break')
					, $this->_draft['drd_message']);
			foreach ($arr as $k => $v) {
				$msg .= ($k + 1) . ' ' . $v . "\n";
			}
		}

		// 取最近一次操作相关人员
		$uids = array();
		if (! empty($this->_draft['drd_cc_uid'])) {
			$uids = explode(',', $this->_draft['drd_cc_uid']);
		}
        	$a_uids = array();
		if (! empty($this->_draft['drd_a_uid'])) {
		    $a_uids = explode(',', $this->_draft['drd_a_uid']);
			$uids = array_merge($uids, $a_uids);
		}

		// 取用户信息
		$serv_m = &service::factory('voa_s_oa_member', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$users = $serv_m->fetch_all_by_ids($uids);

		// 输出接收人
		$ret = array();
		$ret['accepter'] = $users;
		if (! empty($this->_draft['drd_a_uid'])) {
			//$ret['accepter'] = $users[$this->_draft['drd_a_uid']];
			unset($users[$this->_draft['drd_a_uid']]);
		}

		$ret['ccusers'] = $users;
		$ret['message'] = $msg;
		return true;
	}
}
