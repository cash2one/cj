<?php
/**
 * 会议
 * $Author$
 * $Id$
 */

class voa_c_frontend_meeting_base extends voa_c_frontend_base {
	/** 会议状态 */
	const ST_NORMAL = 0;
	const ST_FIN = 1;
	const ST_CANCEL = 2;
	/** 房间信息 */
	protected $_rooms = array();
	/** 状态信息数组 */
	protected $_st_tips = array(
		0 => '正常',
		1 => '已结束',
		2 => '已取消'
	);

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_mobile_tpl = true;

		$this->_rooms = voa_h_cache::get_instance()->get('plugin.meeting.room', 'oa', true);
		if (empty($this->_rooms)) {
			$this->_error_message('请先添加会议室信息');
		}

		$this->view->set('navtitle', '订会议室');

		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.meeting.setting', 'oa');

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
	 * 获取会议室配置
	 */
	public static function fetch_cache_meeting_room() {
		$serv = &service::factory('voa_s_oa_meeting_room', array('pluginid' => startup_env::get('pluginid')));
		return $serv->fetch_all();
	}

	/**
	 * 获取会议配置信息
	 */
	public static function fetch_cache_meeting_setting() {

		$serv = &service::factory('voa_s_oa_meeting_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->fetch_all();
		$arr = array();
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['ms_type']) {
				$arr[$v['ms_key']] = unserialize($v['ms_value']);
			} else {
				$arr[$v['ms_key']] = $v['ms_value'];
			}
		}

		self::_check_agentid($arr, 'meeting');
		return $arr;
	}

	/** 判断当前值是否为空, 或者状态是否正常 */
	protected function _meeting_is_valid($meeting) {
		/** 正常状态值 */
		$sts = array(voa_d_oa_meeting::STATUS_NORMAL, voa_d_oa_meeting::STATUS_UPDATE);
		if (empty($meeting) || !in_array($meeting['mt_status'], $sts) || $meeting['mt_endtime'] < startup_env::get('timestamp')) {
			return false;
		}

		return true;
	}

	/** 显示操作菜单 */
	public static function show_menu($data, $plugin) {
		$serv = voa_wxqy_service::instance();
		/** 取草稿内容 */
		$content = $data['content'];
		$serv_dr = &service::factory('voa_s_oa_meeting_draft', array('pluginid' => startup_env::get('pluginid')));
		$draft = $serv_dr->get_by_openid($data['from_user_name']);
		if (!empty($draft['mtd_message'])) {
			$content = $draft['mtd_message'].config::get(startup_env::get('app_name').'.page_break').$content;
		}

		/** 更新草稿内容 */
		if (empty($draft)) {
			$serv_dr->insert(array(
				'm_openid' => $data['from_user_name'],
				'mtd_message' => $content
			));
		} else {
			$serv_dr->update(array('mtd_message' => $content), array('m_openid' => $data['from_user_name']));
		}

		/** 整理段落序号 */
		$arr = explode(config::get(startup_env::get('app_name').'.page_break'), $content);
		$msg = '';
		foreach ($arr as $k => $v) {
			$msg .= ($k + 1).' '.$v."\n";
		}

		$viewurl = $serv->oauth_url_base(voa_h_func::get_agent_url('/meeting/new', $plugin['cp_pluginid']));
		$ret = "会议议题：\n"
			 . $msg."\n"
			 . "===操作===\n"
			 . ' <a href="'.$viewurl.'">发起会议</a> 快速预订'."\n"
			 . "没写完可以接着写哦";

		return $ret;
	}

	/**
	 * 获取查看详情的url
	 * @param string $url url地址
	 * @param int $mt_id 微信墙信息id
	 * @return boolean
	 */
	public function get_view_url(&$url, $mt_id) {
		/** 组织查看链接 */
		$scheme = config::get('voa.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($scheme.$this->_setting['domain'].'/meeting/view/'.$mt_id.'?pluginid='.startup_env::get('pluginid'));

		return true;
	}

	/**
	 * 更新草稿
	 * @param array $cc_uids 抄送人uids
	 */
	protected function _update_draft($cc_uids = array()) {
		$serv = &service::factory('voa_s_oa_meeting_draft', array('pluginid' => startup_env::get('pluginid')));
		$rec = $serv->get_by_openid($this->_user['m_openid']);
		if ($rec) {
			$serv->update(array(
				'mtd_message' => '',
				'mtd_cc_uid' => implode(',', array_diff($cc_uids, array($this->_user['m_uid'])))
			), array('m_openid' => $this->_user['m_openid']));
		} else {
			$serv->insert(array(
				'm_openid' => $this->_user['m_openid'],
				'mtd_cc_uid' => implode(',', array_diff($cc_uids, array($this->_user['m_uid'])))
			));
		}

		return true;
	}

	/**
	 * 获取草稿信息
	 * @param array &$ret 草稿内容
	 */
	protected function _get_draft(&$ret) {
		$serv_dr = &service::factory('voa_s_oa_meeting_draft', array('pluginid' => startup_env::get('pluginid')));
		$this->_draft = $serv_dr->get_by_openid($this->_user['m_openid']);
		if (empty($this->_draft)) {
			$this->_draft = $serv_dr->fetch_all_field();
		}

		$this->view->set('mtd_id', $this->_draft['mtd_id']);
		/** 整理段落序号 */
		$msg = '';
		if (!empty($this->_draft['mtd_message'])) {
			$arr = explode(config::get(startup_env::get('app_name').'.page_break'), $this->_draft['mtd_message']);
			foreach ($arr as $k => $v) {
				$msg .= ($k + 1).' '.$v."\n";
			}
		}

		/** 取最近一次操作相关人员 */
		$uids = array();
		if (!empty($this->_draft['mtd_cc_uid'])) {
			$uids = explode(',', $this->_draft['mtd_cc_uid']);
		}

		/** 取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
		$users = $serv_m->fetch_all_by_ids($uids);
		foreach ($users as & $u)
		{
			$u = $u['m_username'];
		}

		$ret = array('ccusers' => $users, 'message' => $msg);
		return true;
	}

	//显示ajax信息
	public function ajax($state, $info = '')
	{
		echo json_encode(array('state' => $state, 'info' => $info));
		exit;
	}
}

